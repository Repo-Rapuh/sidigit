<?php

namespace App\Livewire\Admin\Productions;

use App\Models\ProductionJob;
use App\Models\User;
use App\Services\ProductionJobService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Produksi Kanban')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;

    public const COLUMN_DESAIN = 'desain';

    public string $search = '';

    public bool $showQcFailModal = false;
    public ?int $qcFailJobId = null;
    public ?string $qcFailNote = null;
    public bool $showTaskDetailModal = false;
    public ?array $taskDetail = null;

    public array $userRoleSlugs = [];
    protected ProductionJobService $service;

    public function boot(ProductionJobService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('production.view');

        $this->userRoleSlugs = auth()->user()?->roles()->pluck('slug')->all() ?? [];

        $this->setPageMeta(
            'Produksi Kanban',
            'Kelola task produksi per item order dalam board Kanban.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produksi', 'url' => route('productions.index'), 'current' => true],
            ]
        );
    }

    public function getColumnsProperty(): array
    {
        return [
            ProductionJob::STATUS_ANTRIAN => ['label' => 'Antrian', 'accent' => 'border-gray-200 dark:border-gray-800'],
            self::COLUMN_DESAIN => ['label' => 'Desain', 'accent' => 'border-cyan-200 dark:border-cyan-900/50'],
            ProductionJob::STATUS_IN_PROGRESS => ['label' => 'Produksi', 'accent' => 'border-blue-200 dark:border-blue-900/50'],
            ProductionJob::STATUS_QC => ['label' => 'QC', 'accent' => 'border-amber-200 dark:border-amber-900/50'],
            ProductionJob::STATUS_SIAP_DIAMBIL => ['label' => 'Siap Diambil', 'accent' => 'border-violet-200 dark:border-violet-900/50'],
        ];
    }

    public function getGroupedJobsProperty(): array
    {
        $jobs = $this->service->kanbanQuery(null, $this->search)
            ->get()
            ->filter(fn (ProductionJob $job) => $this->isVisibleForCurrentRole($job));
        $grouped = [];

        foreach (array_keys($this->columns) as $column) {
            $grouped[$column] = collect();
        }

        foreach ($jobs as $job) {
            $column = $this->resolveBoardColumn($job);
            $grouped[$column]->push($job);
        }

        foreach (array_keys($this->columns) as $column) {
            $grouped[$column] = $grouped[$column]
                ->sortBy(fn (ProductionJob $job) => [
                    $this->priorityRank($job),
                    $this->deadlineSortValue($job),
                    -((int) ($job->updated_at?->getTimestamp() ?? 0)),
                ])
                ->values();
        }

        return $grouped;
    }

    public function claim(int $jobId): void
    {
        $this->authorize('production.edit');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->claimJob($jobId, $actor);
            $this->dispatch('toast', message: 'Task berhasil diambil.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal mengambil task.');
        }
    }

    public function release(int $jobId): void
    {
        $this->authorize('production.edit');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->releaseJob($jobId, $actor);
            $this->dispatch('toast', message: 'Task berhasil dilepas ke antrian role.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal melepas task.');
        }
    }

    public function markInProgress(int $jobId): void
    {
        $this->authorize('production.edit');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->markInProgress($jobId, null, $actor);
            $this->dispatch('toast', message: 'Task dipindahkan ke Produksi.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memulai task.');
        }
    }

    public function moveToQc(int $jobId): void
    {
        $this->authorize('production.qc');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->moveToQc($jobId, null, $actor);
            $this->dispatch('toast', message: 'Task dipindahkan ke QC.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memindahkan task ke QC.');
        }
    }

    public function qcPass(int $jobId): void
    {
        $this->authorize('production.qc');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->qcPass($jobId, null, $actor);
            $this->dispatch('toast', message: 'QC lulus. Task siap diambil.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menyimpan hasil QC.');
        }
    }

    public function moveCard(int $jobId, string $toColumn): void
    {
        $actor = auth()->user();
        if (!$actor instanceof User) {
            abort(403);
        }

        $allowedColumns = [
            ProductionJob::STATUS_ANTRIAN,
            self::COLUMN_DESAIN,
            ProductionJob::STATUS_IN_PROGRESS,
            ProductionJob::STATUS_QC,
            ProductionJob::STATUS_SIAP_DIAMBIL,
        ];

        if (!in_array($toColumn, $allowedColumns, true)) {
            $this->dispatch('toast', message: 'Target kolom tidak valid.', type: 'warning');
            return;
        }

        $job = $this->service->find($jobId);
        $fromColumn = $this->resolveBoardColumn($job);
        if ($fromColumn === $toColumn) {
            return;
        }

        try {
            if (in_array($toColumn, [ProductionJob::STATUS_QC, ProductionJob::STATUS_SIAP_DIAMBIL], true)) {
                $this->authorize('production.qc');
            } else {
                $this->authorize('production.edit');
            }

            if ($toColumn === self::COLUMN_DESAIN) {
                if ($fromColumn !== ProductionJob::STATUS_ANTRIAN) {
                    throw ValidationException::withMessages([
                        'status' => 'Task hanya bisa masuk tahap Desain dari Antrian.',
                    ]);
                }

                $this->service->switchStage($jobId, ProductionJob::STAGE_DESAIN, 'Masuk tahap desain.');
                $this->service->markInProgress($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke Desain.', type: 'success');
                return;
            }

            if ($toColumn === ProductionJob::STATUS_IN_PROGRESS) {
                if ($fromColumn === self::COLUMN_DESAIN) {
                    $this->service->switchStage($jobId, ProductionJob::STAGE_PRODUKSI, 'Desain selesai. Lanjut ke produksi.');
                    $jobAfterSwitch = $this->service->find($jobId);

                    if ($this->canHandleRole($jobAfterSwitch)) {
                        $this->service->markInProgress($jobId, null, $actor);
                        $this->dispatch('toast', message: 'Task dipindahkan ke Produksi.', type: 'success');
                        return;
                    }

                    $this->dispatch('toast', message: 'Task dipindahkan ke Produksi. Menunggu operator mengambil task.', type: 'success');
                    return;
                }

                if ($fromColumn === ProductionJob::STATUS_ANTRIAN) {
                    $this->service->switchStage($jobId, ProductionJob::STAGE_PRODUKSI, 'Bypass desain. Langsung ke produksi.');
                } elseif ($fromColumn !== ProductionJob::STATUS_IN_PROGRESS) {
                    throw ValidationException::withMessages([
                        'status' => 'Task hanya bisa masuk Produksi dari Antrian atau Desain.',
                    ]);
                }

                $this->service->markInProgress($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke Produksi.', type: 'success');
                return;
            }

            if ($toColumn === ProductionJob::STATUS_QC) {
                if ($fromColumn !== ProductionJob::STATUS_IN_PROGRESS) {
                    throw ValidationException::withMessages([
                        'status' => 'Task hanya bisa masuk QC dari Produksi.',
                    ]);
                }

                $this->service->moveToQc($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke QC.', type: 'success');
                return;
            }

            if ($toColumn === ProductionJob::STATUS_SIAP_DIAMBIL) {
                $this->service->qcPass($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke Siap Diambil.', type: 'success');
                return;
            }

            $this->dispatch('toast', message: 'Drag ke kolom ini belum didukung.', type: 'warning');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memindahkan task via drag.');
        }
    }

    public function openQcFail(int $jobId): void
    {
        $this->authorize('production.qc');

        $this->qcFailJobId = $jobId;
        $this->qcFailNote = null;
        $this->showQcFailModal = true;
    }

    public function saveQcFail(): void
    {
        $this->authorize('production.qc');

        try {
            $validated = $this->validate([
                'qcFailNote' => ['required', 'string', 'min:5', 'max:500'],
            ], [
                'qcFailNote.required' => 'Catatan QC gagal wajib diisi.',
                'qcFailNote.min' => 'Catatan QC gagal minimal :min karakter.',
                'qcFailNote.max' => 'Catatan QC gagal maksimal :max karakter.',
            ], [
                'qcFailNote' => 'catatan QC gagal',
            ]);

            if (!$this->qcFailJobId) {
                return;
            }

            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->qcFail($this->qcFailJobId, $validated['qcFailNote'], $actor);
            $this->dispatch('toast', message: 'QC gagal, task kembali ke Produksi.', type: 'warning');
            $this->closeModal();
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memproses QC gagal.');
        }
    }

    public function closeModal(): void
    {
        $this->showQcFailModal = false;
        $this->qcFailJobId = null;
        $this->qcFailNote = null;
        $this->resetValidation();
    }

    public function openTaskDetail(int $jobId): void
    {
        $this->authorize('production.view');

        $job = $this->service->find($jobId);

        $this->taskDetail = [
            'tracking_id' => '#PJ-' . str_pad((string) $job->id, 6, '0', STR_PAD_LEFT),
            'order_no' => (string) ($job->order?->order_no ?? '-'),
            'customer_name' => (string) ($job->order?->customer?->name ?? 'Umum'),
            'status' => $this->columnLabelForJob($job),
            'product_name' => (string) ($job->orderItem?->product?->name ?? '-'),
            'qty' => number_format((float) ($job->orderItem?->qty ?? 0), 0, ',', '.'),
            'material' => (string) ($job->orderItem?->material?->name ?? '-'),
            'size' => $this->sizeLabel($job),
            'finishes' => $job->orderItem?->finishes
                ? $job->orderItem->finishes
                    ->map(fn ($finish) => $finish->finish?->name)
                    ->filter()
                    ->values()
                    ->all()
                : [],
            'deadline' => $this->deadlineDisplay($job),
            'deadline_countdown' => $this->deadlineCountdown($job),
            'priority' => $this->priorityMeta($job)['label'],
            'claim' => $this->claimMeta($job)['label'],
            'order_notes' => (string) ($job->order?->notes ?? ''),
            'job_notes' => (string) ($job->notes ?? ''),
            // Placeholder sampai field lampiran order ditambahkan.
            'attachments' => [],
        ];

        $this->showTaskDetailModal = true;
    }

    public function closeTaskDetail(): void
    {
        $this->showTaskDetailModal = false;
        $this->taskDetail = null;
    }

    public function isMine(ProductionJob $job): bool
    {
        return (int) ($job->claimed_by ?? 0) === (int) (auth()->id() ?? 0);
    }

    public function canManageAll(): bool
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return false;
        }

        return $user->hasRoleSlug(['owner', 'superadmin', 'administrator', 'admin']) || $user->can('production.assign');
    }

    public function roleScopeLabel(): ?string
    {
        $stage = $this->focusedStageForCurrentRole();
        if (!$stage) {
            return null;
        }

        return $stage === ProductionJob::STAGE_DESAIN
            ? 'Mode Desainer: menampilkan task tahap Desain'
            : 'Mode Operator: menampilkan task tahap Produksi';
    }

    public function canHandleRole(ProductionJob $job): bool
    {
        if ($this->canManageAll()) {
            return true;
        }

        $slug = $job->assignedRole?->slug;
        if (!filled($slug)) {
            return true;
        }

        return in_array($slug, $this->userRoleSlugs, true);
    }

    public function canClaim(ProductionJob $job): bool
    {
        if (!$this->canHandleRole($job)) {
            return false;
        }

        return empty($job->claimed_by) || $this->isMine($job);
    }

    public function canRelease(ProductionJob $job): bool
    {
        if (empty($job->claimed_by)) {
            return false;
        }

        return $this->isMine($job) || $this->canManageAll();
    }

    public function canMove(ProductionJob $job): bool
    {
        if (empty($job->claimed_by)) {
            return $this->canClaim($job);
        }

        return $this->isMine($job) || $this->canManageAll();
    }

    public function canStartFromQueue(ProductionJob $job): bool
    {
        return $job->status === ProductionJob::STATUS_ANTRIAN
            && !empty($job->claimed_by)
            && $this->canMove($job);
    }

    public function queueStartLabel(ProductionJob $job): string
    {
        return (string) $job->stage === ProductionJob::STAGE_DESAIN ? 'Mulai Desain' : 'Mulai Produksi';
    }

    public function queueStartTarget(ProductionJob $job): string
    {
        return (string) $job->stage === ProductionJob::STAGE_DESAIN
            ? self::COLUMN_DESAIN
            : ProductionJob::STATUS_IN_PROGRESS;
    }

    public function priorityMeta(ProductionJob $job): array
    {
        $deadline = $this->deadlineDate($job);
        if (!$deadline) {
            return [
                'label' => 'Normal',
                'rank' => 2,
                'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            ];
        }

        $today = now()->startOfDay();
        if ($deadline->lt($today)) {
            return [
                'label' => 'Urgent',
                'rank' => 0,
                'class' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
            ];
        }

        if ($deadline->equalTo($today)) {
            return [
                'label' => 'Today',
                'rank' => 1,
                'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            ];
        }

        return [
            'label' => 'Normal',
            'rank' => 2,
            'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        ];
    }

    public function claimMeta(ProductionJob $job): array
    {
        if (empty($job->claimed_by)) {
            return [
                'label' => 'Belum diambil',
                'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            ];
        }

        if ($this->isMine($job)) {
            return [
                'label' => 'Milik saya',
                'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            ];
        }

        return [
            'label' => 'Diambil ' . ($job->claimedByUser?->name ?? 'User lain'),
            'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
        ];
    }

    public function deadlineDisplay(ProductionJob $job): string
    {
        $deadline = $this->deadlineDate($job);

        return $deadline ? $deadline->format('d M Y') : '-';
    }

    public function deadlineCountdown(ProductionJob $job): string
    {
        $deadline = $this->deadlineDate($job);
        if (!$deadline) {
            return 'Tanpa deadline';
        }

        $today = now()->startOfDay();
        if ($deadline->lt($today)) {
            $lateDays = $deadline->diffInDays($today);

            return 'Lewat ' . $lateDays . ' hari';
        }

        if ($deadline->equalTo($today)) {
            return 'Jatuh tempo hari ini';
        }

        $days = $today->diffInDays($deadline);
        if ($days === 1) {
            return '1 hari lagi';
        }

        return $days . ' hari lagi';
    }

    public function sizeLabel(ProductionJob $job): string
    {
        $length = (float) ($job->orderItem?->length_cm ?? 0);
        $width = (float) ($job->orderItem?->width_cm ?? 0);
        if ($length <= 0 || $width <= 0) {
            return '-';
        }

        return rtrim(rtrim(number_format($length, 2, '.', ''), '0'), '.')
            . ' x '
            . rtrim(rtrim(number_format($width, 2, '.', ''), '0'), '.')
            . ' cm';
    }

    public function render()
    {
        return view('livewire.admin.productions.index');
    }

    protected function resolveBoardColumn(ProductionJob $job): string
    {
        if ($job->status === ProductionJob::STATUS_IN_PROGRESS && $job->stage === ProductionJob::STAGE_DESAIN) {
            return self::COLUMN_DESAIN;
        }

        return (string) $job->status;
    }

    protected function focusedStageForCurrentRole(): ?string
    {
        if ($this->canManageAll()) {
            return null;
        }

        $hasDesainer = in_array('desainer', $this->userRoleSlugs, true) || in_array('designer', $this->userRoleSlugs, true);
        $hasOperator = in_array('operator', $this->userRoleSlugs, true);

        if ($hasDesainer && !$hasOperator) {
            return ProductionJob::STAGE_DESAIN;
        }

        if ($hasOperator && !$hasDesainer) {
            return ProductionJob::STAGE_PRODUKSI;
        }

        return null;
    }

    protected function isVisibleForCurrentRole(ProductionJob $job): bool
    {
        $focusedStage = $this->focusedStageForCurrentRole();
        if ($focusedStage === null) {
            return true;
        }

        if ($this->isMine($job)) {
            return true;
        }

        return (string) $job->stage === $focusedStage;
    }

    protected function priorityRank(ProductionJob $job): int
    {
        return (int) ($this->priorityMeta($job)['rank'] ?? 2);
    }

    protected function deadlineSortValue(ProductionJob $job): int
    {
        $deadline = $this->deadlineDate($job);

        return $deadline?->getTimestamp() ?? PHP_INT_MAX;
    }

    protected function deadlineDate(ProductionJob $job): ?Carbon
    {
        $deadline = $job->order?->deadline;
        if (!$deadline) {
            return null;
        }

        if ($deadline instanceof Carbon) {
            return $deadline->copy()->startOfDay();
        }

        return Carbon::parse((string) $deadline)->startOfDay();
    }

    protected function columnLabelForJob(ProductionJob $job): string
    {
        $column = $this->resolveBoardColumn($job);

        return (string) ($this->columns[$column]['label'] ?? ucfirst(str_replace('_', ' ', (string) $job->status)));
    }
}
