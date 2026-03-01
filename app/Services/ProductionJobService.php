<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductionJob;
use App\Models\ProductionJobLog;
use App\Models\Role;
use App\Models\User;
use App\Repositories\ProductionJobRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductionJobService
{
    protected ProductionJobRepository $repository;
    protected OrderStockService $orderStockService;

    public function __construct(ProductionJobRepository $repository, OrderStockService $orderStockService)
    {
        $this->repository = $repository;
        $this->orderStockService = $orderStockService;
    }

    public function query(): Builder
    {
        return $this->repository->query()
            ->with([
                'order:id,order_no,status,deadline,customer_id,notes',
                'order.customer:id,name,phone_number',
                'orderItem:id,order_id,product_id,material_id,qty,length_cm,width_cm',
                'orderItem.product:id,name',
                'orderItem.material:id,name',
                'orderItem.finishes:id,order_item_id,finish_id,price',
                'orderItem.finishes.finish:id,name',
                'assignedRole:id,name,slug',
                'claimedByUser:id,name',
            ]);
    }

    public function kanbanQuery(?string $stage = null, ?string $search = null): Builder
    {
        $query = $this->query();

        if (filled($stage)) {
            $query->where('stage', $stage);
        }

        if (filled($search)) {
            $keyword = trim((string) $search);
            $query->where(function (Builder $q) use ($keyword) {
                $q->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('order_no', 'like', "%{$keyword}%"))
                    ->orWhereHas('orderItem.product', fn (Builder $productQuery) => $productQuery->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('claimedByUser', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$keyword}%"));
            });
        }

        return $query
            ->orderByRaw("CASE status
                WHEN 'antrian' THEN 1
                WHEN 'in_progress' THEN 2
                WHEN 'qc' THEN 3
                WHEN 'siap_diambil' THEN 4
                ELSE 99
            END")
            ->orderByDesc('updated_at');
    }

    public function switchStage(int $jobId, string $targetStage, ?string $note = null): ProductionJob
    {
        $labels = ProductionJob::stageOptions();
        if (!array_key_exists($targetStage, $labels)) {
            throw ValidationException::withMessages([
                'stage' => 'Tahap produksi tidak valid.',
            ]);
        }

        $job = $this->repository->findOrFail($jobId);
        $currentStage = (string) ($job->stage ?? '');

        if ($currentStage === $targetStage) {
            return $job->fresh(['order', 'assignedRole', 'claimedByUser', 'orderItem.product']);
        }

        $targetRole = $this->resolveRoleForStage($targetStage);
        $targetRoleId = $targetRole?->id;

        $this->repository->update($job, [
            'stage' => $targetStage,
            'assigned_role_id' => $targetRoleId,
            // Reset claim agar perpindahan antar tahap tetap aman terhadap role.
            'claimed_by' => null,
            'claimed_at' => null,
        ]);

        $job->refresh();

        $this->createLog(
            $job,
            'stage_switched',
            (string) $job->status,
            (string) $job->status,
            $note ?: ('Tahap job dipindahkan ke ' . ($labels[$targetStage] ?? ucfirst($targetStage)) . '.')
        );

        if ($targetRoleId) {
            $this->createLog(
                $job,
                'auto_assigned',
                (string) $job->status,
                (string) $job->status,
                'Auto-assign ke role: ' . ($targetRole?->name ?? 'Role') . '.'
            );
        }

        $this->syncOrderStatusFromJobs($job->order()->firstOrFail());

        return $job->fresh(['order', 'assignedRole', 'claimedByUser', 'orderItem.product']);
    }

    public function find(int $id): ProductionJob
    {
        return $this->query()->findOrFail($id);
    }

    public function syncByOrderStatus(Order $order): void
    {
        $targetStage = $this->resolveStageByOrderStatus((string) $order->status);
        if (!$targetStage) {
            return;
        }

        $targetRole = $this->resolveRoleForStage($targetStage);
        $targetRoleId = $targetRole?->id;

        $order->loadMissing(['items.product']);

        foreach ($order->items as $item) {
            $job = $this->repository->query()
                ->where('order_item_id', $item->id)
                ->first();

            if (!$job) {
                $job = $this->repository->create([
                    'branch_id' => $order->branch_id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'stage' => $targetStage,
                    'assigned_role_id' => $targetRoleId,
                    'claimed_by' => null,
                    'claimed_at' => null,
                    'status' => ProductionJob::STATUS_ANTRIAN,
                    'notes' => 'Job produksi otomatis dibuat dari status order.',
                ]);

                $this->createLog(
                    $job,
                    'created',
                    null,
                    ProductionJob::STATUS_ANTRIAN,
                    'Job dibuat otomatis untuk tahap ' . $this->stageLabel($targetStage) . '.'
                );

                if ($targetRoleId) {
                    $this->createLog(
                        $job,
                        'auto_assigned',
                        ProductionJob::STATUS_ANTRIAN,
                        ProductionJob::STATUS_ANTRIAN,
                        'Auto-assign ke role: ' . ($targetRole?->name ?? 'Role') . '.'
                    );
                }

                continue;
            }

            $fromStatus = (string) $job->status;
            $updates = [];
            $stageChanged = false;
            $roleChanged = false;

            if ((string) $job->stage !== $targetStage) {
                $updates['stage'] = $targetStage;
                $updates['status'] = ProductionJob::STATUS_ANTRIAN;
                $updates['claimed_by'] = null;
                $updates['claimed_at'] = null;
                $stageChanged = true;
            }

            if ($targetRoleId && (int) $job->assigned_role_id !== (int) $targetRoleId) {
                $updates['assigned_role_id'] = $targetRoleId;
                $roleChanged = true;
            }

            if (empty($updates)) {
                continue;
            }

            $this->repository->update($job, $updates);
            $job->refresh();

            if ($stageChanged) {
                $this->createLog(
                    $job,
                    'stage_switched',
                    $fromStatus,
                    (string) $job->status,
                    'Tahap job dipindahkan ke ' . $this->stageLabel($targetStage) . ' dari perubahan status order.'
                );
            }

            if ($roleChanged) {
                $this->createLog(
                    $job,
                    'auto_assigned',
                    (string) $job->status,
                    (string) $job->status,
                    'Auto-assign ke role: ' . ($targetRole?->name ?? 'Role') . '.'
                );
            }
        }
    }

    public function claimJob(int $jobId, User $actor): ProductionJob
    {
        $job = $this->repository->query()->with('assignedRole')->findOrFail($jobId);

        if (!$this->canUserHandleJobRole($actor, $job)) {
            throw ValidationException::withMessages([
                'claim' => 'Anda tidak memiliki role yang sesuai untuk mengambil task ini.',
            ]);
        }

        if (!empty($job->claimed_by) && (int) $job->claimed_by !== (int) $actor->id) {
            throw ValidationException::withMessages([
                'claim' => 'Task sudah diambil oleh user lain.',
            ]);
        }

        if ((int) ($job->claimed_by ?? 0) === (int) $actor->id) {
            return $job->fresh(['assignedRole', 'claimedByUser', 'order', 'orderItem.product']);
        }

        $this->repository->update($job, [
            'claimed_by' => $actor->id,
            'claimed_at' => now(),
        ]);

        $job->refresh();

        $this->createLog(
            $job,
            'claimed',
            (string) $job->status,
            (string) $job->status,
            'Task diambil oleh ' . $actor->name . '.'
        );

        return $job->fresh(['assignedRole', 'claimedByUser', 'order', 'orderItem.product']);
    }

    public function releaseJob(int $jobId, User $actor): ProductionJob
    {
        $job = $this->repository->query()->findOrFail($jobId);

        if (empty($job->claimed_by)) {
            return $job->fresh(['assignedRole', 'claimedByUser', 'order', 'orderItem.product']);
        }

        if (!$this->isManager($actor) && (int) $job->claimed_by !== (int) $actor->id) {
            throw ValidationException::withMessages([
                'release' => 'Task ini bukan claim Anda.',
            ]);
        }

        $this->repository->update($job, [
            'claimed_by' => null,
            'claimed_at' => null,
        ]);

        $job->refresh();

        $this->createLog(
            $job,
            'released',
            (string) $job->status,
            (string) $job->status,
            'Claim task dilepas oleh ' . $actor->name . '.'
        );

        return $job->fresh(['assignedRole', 'claimedByUser', 'order', 'orderItem.product']);
    }

    public function assignRole(int $jobId, ?int $roleId): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        if ($roleId !== null) {
            $roleExists = Role::query()->where('id', $roleId)->exists();
            if (!$roleExists) {
                throw ValidationException::withMessages([
                    'assign_role_id' => 'Role penanggung jawab tidak valid.',
                ]);
            }
        }

        $this->repository->update($job, [
            'assigned_role_id' => $roleId,
            'claimed_by' => null,
            'claimed_at' => null,
        ]);

        $roleName = $roleId ? (Role::query()->find($roleId)?->name ?? 'Role') : 'Tanpa role';
        $this->createLog($job, 'assigned', $job->status, $job->status, 'Assign role: ' . $roleName . '.');

        return $job->fresh(['assignedRole', 'claimedByUser']);
    }

    public function markInProgress(int $jobId, ?string $note = null, ?User $actor = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        if ($actor && empty($job->claimed_by)) {
            $this->claimJob($jobId, $actor);
            $job = $this->repository->findOrFail($jobId);
        }

        if ($actor) {
            $this->ensureActorCanMove($actor, $job);
        }

        $this->ensureTransitionAllowed($job->status, [
            ProductionJob::STATUS_ANTRIAN,
            ProductionJob::STATUS_IN_PROGRESS,
        ]);

        return $this->transition($job, ProductionJob::STATUS_IN_PROGRESS, 'in_progress', $note ?: 'Job diproses.');
    }

    public function moveToQc(int $jobId, ?string $note = null, ?User $actor = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        if ($actor) {
            $this->ensureActorCanMove($actor, $job);
        }

        $this->ensureTransitionAllowed($job->status, [ProductionJob::STATUS_IN_PROGRESS]);

        return $this->transition($job, ProductionJob::STATUS_QC, 'to_qc', $note ?: 'Item masuk tahap QC.');
    }

    public function qcPass(int $jobId, ?string $note = null, ?User $actor = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        if ($actor) {
            $this->ensureActorCanMove($actor, $job);
        }

        $this->ensureTransitionAllowed($job->status, [ProductionJob::STATUS_QC]);

        return $this->transition($job, ProductionJob::STATUS_SIAP_DIAMBIL, 'qc_pass', $note ?: 'QC lulus.');
    }

    public function qcFail(int $jobId, ?string $note = null, ?User $actor = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        if ($actor) {
            $this->ensureActorCanMove($actor, $job);
        }

        $this->ensureTransitionAllowed($job->status, [ProductionJob::STATUS_QC]);

        return $this->transition($job, ProductionJob::STATUS_IN_PROGRESS, 'qc_fail', $note ?: 'QC gagal. Kembali ke produksi.');
    }

    public function recentLogsForJob(int $jobId, int $limit = 15): Collection
    {
        return ProductionJobLog::query()
            ->with('changedByUser:id,name')
            ->where('production_job_id', $jobId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    protected function transition(ProductionJob $job, string $toStatus, string $event, ?string $note = null): ProductionJob
    {
        $fromStatus = (string) $job->status;

        $updates = [
            'status' => $toStatus,
            'notes' => $note ?: $job->notes,
        ];

        if ($toStatus === ProductionJob::STATUS_SIAP_DIAMBIL) {
            $updates['claimed_by'] = null;
            $updates['claimed_at'] = null;
        }

        $this->repository->update($job, $updates);

        $job->refresh();

        $this->createLog($job, $event, $fromStatus, $toStatus, $note);
        $this->syncOrderStatusFromJobs($job->order()->firstOrFail());

        return $job->fresh(['order', 'assignedRole', 'claimedByUser', 'orderItem.product']);
    }

    protected function syncOrderStatusFromJobs(Order $order): void
    {
        if (!in_array((string) $order->status, ['desain', 'produksi', 'qc', 'siap'], true)) {
            return;
        }

        $jobs = $this->repository->query()
            ->where('order_id', $order->id)
            ->get(['stage', 'status']);

        if ($jobs->isEmpty()) {
            return;
        }

        $targetStatus = 'produksi';

        if ($jobs->contains(fn ($job) => (string) $job->stage === ProductionJob::STAGE_DESAIN)) {
            $targetStatus = 'desain';
        } else {
            $statuses = $jobs->pluck('status');

            if ($statuses->every(fn ($status) => $status === ProductionJob::STATUS_SIAP_DIAMBIL)) {
                $targetStatus = 'siap';
            } elseif ($statuses->every(fn ($status) => in_array($status, [ProductionJob::STATUS_QC, ProductionJob::STATUS_SIAP_DIAMBIL], true))) {
                $targetStatus = 'qc';
            } else {
                $targetStatus = 'produksi';
            }
        }

        if ((string) $order->status === $targetStatus) {
            return;
        }

        $oldStatus = (string) $order->status;
        $order->update(['status' => $targetStatus]);

        $order->statusLogs()->create([
            'status' => $targetStatus,
            'changed_by' => auth()->id(),
            'note' => "Status sinkron dari progres produksi ({$oldStatus} -> {$targetStatus}).",
        ]);

        // Perubahan status dari board produksi juga harus sinkron ke pergerakan stok order.
        $this->orderStockService->syncByStatus($order);
    }

    protected function resolveStageByOrderStatus(string $orderStatus): ?string
    {
        return match ($orderStatus) {
            'desain' => ProductionJob::STAGE_DESAIN,
            'produksi' => ProductionJob::STAGE_PRODUKSI,
            default => null,
        };
    }

    protected function resolveRoleForStage(string $stage): ?Role
    {
        $slugCandidates = $stage === ProductionJob::STAGE_DESAIN
            ? ['desainer', 'designer']
            : ['operator', 'produksi-operator'];

        $role = Role::query()->whereIn('slug', $slugCandidates)->first();
        if ($role) {
            return $role;
        }

        $nameCandidates = $stage === ProductionJob::STAGE_DESAIN
            ? ['Desainer', 'Designer']
            : ['Operator'];

        return Role::query()->whereIn('name', $nameCandidates)->first();
    }

    protected function ensureActorCanMove(User $actor, ProductionJob $job): void
    {
        if ($this->isManager($actor)) {
            return;
        }

        if ((int) ($job->claimed_by ?? 0) === (int) $actor->id) {
            return;
        }

        throw ValidationException::withMessages([
            'claim' => 'Ambil task ini terlebih dahulu sebelum mengubah status.',
        ]);
    }

    protected function canUserHandleJobRole(User $actor, ProductionJob $job): bool
    {
        if ($this->isManager($actor)) {
            return true;
        }

        $assignedRole = $job->assignedRole;
        if (!$assignedRole) {
            return true;
        }

        if (!filled($assignedRole->slug)) {
            return true;
        }

        return $actor->hasRoleSlug([$assignedRole->slug]);
    }

    protected function isManager(User $actor): bool
    {
        if ($actor->hasRoleSlug(['owner', 'superadmin', 'administrator', 'admin'])) {
            return true;
        }

        return $actor->can('production.assign');
    }

    protected function stageLabel(string $stage): string
    {
        $labels = ProductionJob::stageOptions();

        return $labels[$stage] ?? ucfirst($stage);
    }

    protected function createLog(ProductionJob $job, string $event, ?string $fromStatus, ?string $toStatus, ?string $note = null): void
    {
        ProductionJobLog::query()->create([
            'branch_id' => $job->branch_id,
            'production_job_id' => $job->id,
            'order_id' => $job->order_id,
            'event' => $event,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'changed_by' => auth()->id(),
        ]);
    }

    protected function ensureTransitionAllowed(string $currentStatus, array $allowedStatuses): void
    {
        if (in_array($currentStatus, $allowedStatuses, true)) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => 'Transisi status produksi tidak valid untuk kondisi saat ini.',
        ]);
    }
}
