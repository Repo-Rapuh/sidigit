<?php

namespace App\Livewire\Admin\Customers;

use App\Livewire\BaseTable;
use App\Services\CustomerService;

class Table extends BaseTable
{
    protected CustomerService $service;

    public array $filters = [
        'member_type' => null,
    ];

    public function boot(CustomerService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        $query = $this->applySearch(
            $this->service->query(),
            ['name', 'phone_number', 'email', 'address']
        );

        if (!empty($this->filters['member_type'])) {
            $query->where('member_type', $this->filters['member_type']);
        }

        return $query;
    }

    public function getMemberTypeOptionsProperty(): array
    {
        return $this->service->memberTypes();
    }

    protected function resetForm(): void
    {
        // No modal form for customers.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for customers.
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Pelanggan berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus pelanggan.', type: 'error');
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
            $this->dispatch('toast', message: 'Pelanggan terpilih berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus pelanggan terpilih.', type: 'error');
        }
    }

    public function goCreate(): void
    {
        $this->redirectRoute('customers.create');
    }

    public function goEdit(int $id): void
    {
        $this->redirectRoute('customers.edit', ['customer' => $id]);
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
            ['label' => 'Tambah Pelanggan', 'method' => 'goCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Nama', 'field' => 'name', 'sortable' => true],
            ['label' => 'Tipe', 'field' => 'member_type', 'sortable' => false, 'view' => 'livewire.admin.customers.columns.member-type'],
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
        return 'livewire.admin.customers.filters';
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'member_type' => null,
        ];
        $this->resetPage();
    }
}
