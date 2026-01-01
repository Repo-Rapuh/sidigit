<?php

namespace App\Livewire\Admin\Warehouses;

use App\Livewire\BaseTable;
use App\Services\WarehouseService;

class Table extends BaseTable
{
    protected WarehouseService $service;

    public function boot(WarehouseService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->query(),
            ['name', 'description']
        );
    }

    protected function resetForm(): void
    {
        // No modal form for warehouses.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for warehouses.
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Gudang berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus gudang.', type: 'error');
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
            $this->dispatch('toast', message: 'Gudang terpilih berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus gudang terpilih.', type: 'error');
        }
    }

    public function goCreate(): void
    {
        $this->redirectRoute('warehouses.create');
    }

    public function goEdit(int $id): void
    {
        $this->redirectRoute('warehouses.edit', ['warehouse' => $id]);
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
            ['label' => 'Tambah Gudang', 'method' => 'goCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            [
                'label' => 'Koordinat',
                'field' => 'location_lat',
                'sortable' => false,
                'format' => fn ($row) => ($row->location_lat !== null && $row->location_lng !== null)
                    ? sprintf('%.6f, %.6f', $row->location_lat, $row->location_lng)
                    : '-',
            ],
            [
                'label' => 'Deskripsi',
                'field' => 'description',
                'sortable' => false,
                'format' => fn ($row) => $row->description
                    ? \Illuminate\Support\Str::limit($row->description, 60)
                    : '-',
            ],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
