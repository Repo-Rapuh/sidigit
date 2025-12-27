<?php

namespace App\Livewire\Admin\Employees;

use App\Livewire\BaseTable;
use App\Services\EmployeeService;

class Table extends BaseTable
{
    protected EmployeeService $service;

    public array $filters = [
        'status' => null,
    ];

    public function boot(EmployeeService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        $query = $this->applySearch(
            $this->service->query(),
            ['name', 'phone_number', 'email', 'address']
        );

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query;
    }

    public function getStatusOptionsProperty(): array
    {
        return $this->service->statuses();
    }

    protected function resetForm(): void
    {
        // No modal form for employees.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for employees.
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Karyawan berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus karyawan.', type: 'error');
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Pilih minimal 1 data.', type: 'warning');
            return;
        }

        try {
            $this->service->destroyMany($this->selected);
            $this->selected = [];
            $this->selectAll = false;
            $this->closeModal();
            $this->dispatch('toast', message: 'Karyawan terpilih berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus karyawan terpilih.', type: 'error');
        }
    }

    public function goCreate(): void
    {
        $this->redirectRoute('employees.create');
    }

    public function goEdit(int $id): void
    {
        $this->redirectRoute('employees.edit', ['employee' => $id]);
    }

    protected function formView(): ?string
    {
        return null;
    }

    protected function rowActions(): array
    {
        return [
            ['label' => 'Edit', 'method' => 'goEdit', 'class' => 'text-brand-500', 'icon' => 'pencil'],
            ['label' => 'Delete', 'method' => 'confirmDelete', 'class' => 'text-red-600', 'icon' => 'trash-2'],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => 'Tambah Karyawan', 'method' => 'goCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
        ];
    }

    protected function bulkActions(): array
    {
        return [
            'delete' => ['label' => 'Delete selected', 'method' => 'confirmBulkDelete'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Foto', 'field' => 'photo', 'sortable' => false, 'view' => 'livewire.admin.employees.columns.photo'],
            ['label' => 'Nama', 'field' => 'name', 'sortable' => true],
            ['label' => 'Status', 'field' => 'status', 'sortable' => false, 'view' => 'livewire.admin.employees.columns.status'],
            ['label' => 'Telepon', 'field' => 'phone_number', 'sortable' => false],
            ['label' => 'Email', 'field' => 'email', 'sortable' => false],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function filtersView(): ?string
    {
        return 'livewire.admin.employees.filters';
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'status' => null,
        ];
        $this->resetPage();
    }
}
