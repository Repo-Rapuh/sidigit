<?php

namespace App\Livewire\Admin\Productions;

use App\Livewire\BaseTable;
use App\Models\ProductionJob;
use App\Models\Role;
use App\Services\ProductionJobService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    use AuthorizesRequests;

    protected ProductionJobService $service;

    public string $sortField = 'production_jobs.updated_at';
    public string $sortDirection = 'desc';

    public array $filters = [
        'stage' => 'all',
        'status' => 'all',
        'assigned_role_id' => null,
    ];

    public string $modalMode = 'assign'; // assign | qc_fail | history
    public ?int $assign_role_id = null;
    public ?string $qc_fail_note = null;
    public array $historyLogs = [];
    public array $historyMeta = [];

    public function boot(ProductionJobService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        $query = $this->service->query()
            ->leftJoin('orders', 'orders.id', '=', 'production_jobs.order_id')
            ->leftJoin('order_items', 'order_items.id', '=', 'production_jobs.order_item_id')
            ->leftJoin('mst_products', 'mst_products.id', '=', 'order_items.product_id')
            ->leftJoin('roles', 'roles.id', '=', 'production_jobs.assigned_role_id')
            ->select('production_jobs.*')
            ->selectRaw('orders.order_no as order_no')
            ->selectRaw('mst_products.name as product_name')
            ->selectRaw('order_items.qty as item_qty')
            ->selectRaw('roles.name as assigned_role_name');

        $query = $this->applySearch($query, ['orders.order_no', 'mst_products.name', 'roles.name']);

        $status = $this->filters['status'] ?? 'all';
        if ($status !== 'all') {
            $query->where('production_jobs.status', $status);
        }

        $stage = $this->filters['stage'] ?? 'all';
        if ($stage !== 'all') {
            $query->where('production_jobs.stage', $stage);
        }

        $assignedRoleId = $this->filters['assigned_role_id'] ?? null;
        if (!empty($assignedRoleId)) {
            $query->where('production_jobs.assigned_role_id', (int) $assignedRoleId);
        }

        return $query;
    }

    protected function applySorting($query)
    {
        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function getRoleOptionsProperty(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($role) => [
                'id' => $role->id,
                'label' => $role->name,
            ])
            ->all();
    }

    public function getStatusOptionsProperty(): array
    {
        $options = [['value' => 'all', 'label' => 'Semua Status']];

        foreach (ProductionJob::statusOptions() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    public function getStageOptionsProperty(): array
    {
        $options = [['value' => 'all', 'label' => 'Semua Tahap']];

        foreach (ProductionJob::stageOptions() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    protected function resetForm(): void
    {
        $this->modalMode = 'assign';
        $this->assign_role_id = null;
        $this->qc_fail_note = null;
        $this->historyLogs = [];
        $this->historyMeta = [];
    }

    protected function loadForm(int $id): void
    {
        $job = $this->service->find($id);
        $this->assign_role_id = $job->assigned_role_id ? (int) $job->assigned_role_id : null;
        $this->qc_fail_note = null;
    }

    public function openAssignModal(int $id): void
    {
        $this->authorize('production.assign');

        $this->activeId = $id;
        $this->loadForm($id);
        $this->modalMode = 'assign';
        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;
        $this->modalTitle = 'Assign Role Produksi';
        $this->modalActionLabel = 'Simpan Assign';
        $this->modalActionMethod = 'saveModal';
        $this->modalCancelLabel = 'Kembali';
        $this->modalMaxWidth = 'md';
    }

    public function openQcFailModal(int $id): void
    {
        $this->authorize('production.qc');

        $this->activeId = $id;
        $this->loadForm($id);
        $this->modalMode = 'qc_fail';
        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;
        $this->modalTitle = 'QC Gagal';
        $this->modalActionLabel = 'Kembalikan ke Produksi';
        $this->modalActionMethod = 'saveModal';
        $this->modalCancelLabel = 'Kembali';
        $this->modalMaxWidth = 'md';
    }

    public function openHistoryModal(int $id): void
    {
        $this->authorize('production.view');

        $job = $this->service->find($id);
        $status = (string) ($job->status ?? '');
        $statusLabel = ProductionJob::statusOptions()[$status] ?? ucfirst($status);

        $logs = $this->service->recentLogsForJob($id, 20)->reverse()->values();

        $this->activeId = $id;
        $this->modalMode = 'history';
        $this->historyMeta = [
            'tracking_id' => '#PJ-' . str_pad((string) $job->id, 6, '0', STR_PAD_LEFT),
            'status_label' => $statusLabel,
            'status_badge_class' => $this->historyStatusBadgeClass($status),
            'order_no' => $job->order?->order_no ?? '-',
            'item_name' => $job->orderItem?->product?->name ?? '-',
            'qty' => number_format((float) ($job->orderItem?->qty ?? 0), 0, ',', '.'),
        ];

        if ($logs->isEmpty()) {
            $this->historyLogs = [[
                'label' => $statusLabel,
                'subtitle' => 'Status saat ini',
                'date' => $job->updated_at?->format('d M Y') ?? '-',
                'time' => $job->updated_at?->format('H:i') ?? '--:--',
                'icon' => $this->historyIconByStatus($status),
                'is_active' => true,
            ]];
        } else {
            $lastIndex = $logs->count() - 1;
            $this->historyLogs = $logs->map(fn ($log, $index) => [
                'label' => $this->historyEventLabel((string) $log->event, (string) ($log->to_status ?? '')),
                'subtitle' => trim(
                    ($log->note ? $log->note . ' ' : '')
                    . '(oleh ' . ($log->changedByUser?->name ?? 'Sistem') . ')'
                ),
                'date' => $log->created_at?->format('d M Y') ?? '-',
                'time' => $log->created_at?->format('H:i') ?? '--:--',
                'icon' => $this->historyIconByEvent((string) $log->event, (string) ($log->to_status ?? '')),
                'is_active' => (int) $index === $lastIndex,
            ])->all();
        }

        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;
        $this->modalTitle = 'Riwayat Produksi';
        $this->modalActionLabel = 'Tutup';
        $this->modalActionMethod = 'closeModal';
        $this->modalCancelLabel = 'Kembali';
        $this->modalMaxWidth = '2xl';
    }

    public function saveModal(): void
    {
        try {
            if (!$this->activeId) {
                return;
            }

            if ($this->modalMode === 'assign') {
                $this->authorize('production.assign');
                $this->service->assignRole($this->activeId, $this->assign_role_id ?: null);
                $this->dispatch('toast', message: 'Assign role berhasil disimpan.', type: 'success');
                $this->closeModal();
                return;
            }

            if ($this->modalMode === 'qc_fail') {
                $this->authorize('production.qc');
                $this->service->qcFail($this->activeId, $this->qc_fail_note);
                $this->dispatch('toast', message: 'QC gagal. Job kembali ke Produksi.', type: 'warning');
                $this->closeModal();
                return;
            }
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menyimpan perubahan produksi.');
        }
    }

    public function markInProgress(int $id): void
    {
        $this->authorize('production.edit');

        try {
            $this->service->markInProgress($id);
            $this->dispatch('toast', message: 'Job dipindahkan ke Produksi.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal mengubah status job.');
        }
    }

    public function moveToQc(int $id): void
    {
        $this->authorize('production.qc');

        try {
            $this->service->moveToQc($id);
            $this->dispatch('toast', message: 'Job dipindahkan ke tahap QC.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memindahkan job ke QC.');
        }
    }

    public function qcPass(int $id): void
    {
        $this->authorize('production.qc');

        try {
            $this->service->qcPass($id);
            $this->dispatch('toast', message: 'QC lulus. Job siap diambil.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal update hasil QC.');
        }
    }

    protected function toastValidation(ValidationException $e, ?string $fallback = null): void
    {
        $errors = $e->validator->errors()->all();
        if (!empty($errors)) {
            $message = "Periksa input:\n• " . implode("\n• ", $errors);
        } else {
            $message = $fallback ?: 'Periksa kembali input. Ada data yang belum sesuai.';
        }

        $this->dispatch('toast', message: $message, type: 'warning');
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'stage' => 'all',
            'status' => 'all',
            'assigned_role_id' => null,
        ];
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.productions.form';
    }

    protected function filtersView(): ?string
    {
        return 'livewire.admin.productions.filters';
    }

    protected function rowActions(): array
    {
        return [
            [
                'label' => 'Assign Role',
                'method' => 'openAssignModal',
                'class' => 'text-brand-600',
                'icon' => 'user-cog',
                'visible' => fn ($row) => auth()->user()?->can('production.assign') ?? false,
            ],
            [
                'label' => 'Mulai Produksi',
                'method' => 'markInProgress',
                'class' => 'text-indigo-600',
                'icon' => 'play',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_ANTRIAN)
                    && (auth()->user()?->can('production.edit') ?? false),
            ],
            [
                'label' => 'Kirim QC',
                'method' => 'moveToQc',
                'class' => 'text-sky-600',
                'icon' => 'shield-check',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_IN_PROGRESS)
                    && (auth()->user()?->can('production.qc') ?? false),
            ],
            [
                'label' => 'QC Lulus',
                'method' => 'qcPass',
                'class' => 'text-emerald-600',
                'icon' => 'check',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_QC)
                    && (auth()->user()?->can('production.qc') ?? false),
            ],
            [
                'label' => 'QC Gagal',
                'method' => 'openQcFailModal',
                'class' => 'text-amber-600',
                'icon' => 'rotate-ccw',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_QC)
                    && (auth()->user()?->can('production.qc') ?? false),
            ],
            [
                'label' => 'Riwayat',
                'method' => 'openHistoryModal',
                'class' => 'text-gray-700 dark:text-gray-200',
                'icon' => 'history',
                'visible' => fn () => auth()->user()?->can('production.view') ?? false,
            ],
            [
                'label' => 'Lihat Order',
                'url' => fn ($row) => route('orders.edit', ['order' => $row->order_id]),
                'class' => 'text-brand-500',
                'icon' => 'eye',
            ],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Order No', 'view' => 'livewire.admin.productions.columns.order-link', 'sortable' => false],
            [
                'label' => 'Tahap',
                'field' => 'stage',
                'sortable' => false,
                'format' => fn ($row) => ProductionJob::stageOptions()[(string) ($row->stage ?? '')] ?? '-',
            ],
            [
                'label' => 'Item',
                'field' => 'product_name',
                'sortable' => false,
                'format' => fn ($row) => ($row->product_name ?: '-') . ' (Qty: ' . number_format((float) ($row->item_qty ?? 0), 0, ',', '.') . ')',
            ],
            ['label' => 'Status', 'view' => 'livewire.admin.productions.columns.status', 'sortable' => false],
            [
                'label' => 'Penanggung Jawab',
                'field' => 'assigned_role_name',
                'sortable' => false,
                'format' => fn ($row) => $row->assigned_role_name ?: '-',
            ],
            [
                'label' => 'Update Terakhir',
                'field' => 'updated_at',
                'sortable' => false,
                'format' => fn ($row) => $row->updated_at?->format('d M Y H:i') ?: '-',
            ],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return false;
    }

    protected function historyEventLabel(string $event, string $toStatus): string
    {
        return match ($event) {
            'created' => 'Job Dibuat',
            'claimed' => 'Task Diambil',
            'released' => 'Task Dilepas',
            'in_progress' => 'Masuk Produksi',
            'finished' => 'Selesai Produksi',
            'to_qc' => 'Masuk QC',
            'qc_pass' => 'Siap Diambil',
            'qc_fail' => 'QC Gagal',
            'assigned', 'auto_assigned' => 'Assign Role',
            'stage_switched' => 'Pindah Tahap',
            default => ProductionJob::statusOptions()[$toStatus] ?? ucfirst(str_replace('_', ' ', $event)),
        };
    }

    protected function historyIconByEvent(string $event, string $status): string
    {
        return match ($event) {
            'created' => 'file-plus',
            'claimed' => 'user-check',
            'released' => 'corner-up-left',
            'in_progress' => 'play',
            'finished' => 'check-check',
            'to_qc' => 'shield-check',
            'qc_pass' => 'package-check',
            'qc_fail' => 'rotate-ccw',
            'assigned', 'auto_assigned' => 'user-cog',
            'stage_switched' => 'git-compare-arrows',
            default => $this->historyIconByStatus($status),
        };
    }

    protected function historyIconByStatus(string $status): string
    {
        return match ($status) {
            ProductionJob::STATUS_ANTRIAN => 'clock-3',
            ProductionJob::STATUS_IN_PROGRESS => 'play',
            ProductionJob::STATUS_QC => 'shield-check',
            ProductionJob::STATUS_SIAP_DIAMBIL => 'package-check',
            default => 'circle',
        };
    }

    protected function historyStatusBadgeClass(string $status): string
    {
        return match ($status) {
            ProductionJob::STATUS_SIAP_DIAMBIL => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            ProductionJob::STATUS_QC => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            ProductionJob::STATUS_IN_PROGRESS => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }
}
