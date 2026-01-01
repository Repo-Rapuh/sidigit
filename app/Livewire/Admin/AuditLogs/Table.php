<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Livewire\BaseTable;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class Table extends BaseTable
{
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public array $filters = [
        'log_name' => null,
        'causer_id' => null,
    ];

    protected function query()
    {
        $query = Activity::query()->with(['causer', 'subject']);

        $query = $this->applySearch($query, ['description', 'log_name']);

        if (!empty($this->filters['log_name'])) {
            $query->where('log_name', $this->filters['log_name']);
        }

        if (!empty($this->filters['causer_id'])) {
            $query->where('causer_id', $this->filters['causer_id']);
        }

        return $query;
    }

    public function getLogNameOptionsProperty(): array
    {
        return Activity::query()
            ->select('log_name')
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->values()
            ->all();
    }

    public function getUserOptionsProperty()
    {
        return User::query()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'label' => $user->name . ($user->email ? " ({$user->email})" : ''),
            ])
            ->values()
            ->all();
    }

    protected function resetForm(): void
    {
        // No modal form for audit logs.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for audit logs.
    }

    protected function formView(): ?string
    {
        return null;
    }

    protected function rowActions(): array
    {
        return [];
    }

    protected function tableActions(): array
    {
        return [];
    }

    protected function bulkActions(): array
    {
        return [];
    }

    protected function columns(): array
    {
        return [
            [
                'label' => 'Waktu',
                'field' => 'created_at',
                'sortable' => true,
                'format' => fn ($row) => $row->created_at?->format('d M Y, H:i') ?? '-',
            ],
            [
                'label' => 'User',
                'field' => 'causer.name',
                'sortable' => false,
                'format' => fn ($row) => $row->causer?->name ?? 'Sistem',
            ],
            ['label' => 'Aktivitas', 'field' => 'description', 'sortable' => false],
            [
                'label' => 'Objek',
                'field' => 'log_name',
                'sortable' => false,
                'format' => fn ($row) => $row->log_name ?? '-',
            ],
            [
                'label' => 'Detail',
                'field' => 'properties',
                'sortable' => false,
                'view' => 'livewire.admin.audit-logs.columns.detail',
            ],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return false;
    }

    protected function filtersView(): ?string
    {
        return 'livewire.admin.audit-logs.filters';
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'log_name' => null,
            'causer_id' => null,
        ];
        $this->resetPage();
    }
}
