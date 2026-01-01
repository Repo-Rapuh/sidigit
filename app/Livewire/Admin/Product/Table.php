<?php

namespace App\Livewire\Admin\Product;

use App\Livewire\BaseTable;
use App\Services\ProductService;

class Table extends BaseTable
{
    protected ProductService $service;

    public function boot(ProductService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->query(),
            ['sku', 'name']
        );
    }

    protected function resetForm(): void
    {
        // No modal form for products.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for products.
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Produk berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus produk.', type: 'error');
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
            $this->dispatch('toast', message: 'Produk terpilih berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus produk terpilih.', type: 'error');
        }
    }

    public function goCreate(): void
    {
        $this->redirectRoute('products.create');
    }

    public function goEdit(int $id): void
    {
        $this->redirectRoute('products.edit', ['product' => $id]);
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
            ['label' => 'Tambah Produk', 'method' => 'goCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'SKU', 'field' => 'sku', 'sortable' => true],
            ['label' => 'Nama', 'field' => 'name', 'sortable' => true],
            ['label' => 'Kategori', 'field' => 'category.name', 'sortable' => false],
            ['label' => 'Satuan', 'field' => 'unit.name', 'sortable' => false],
            [
                'label' => 'Harga Pokok',
                'field' => 'base_price',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->base_price, 2, ',', '.'),
            ],
            [
                'label' => 'Harga Jual',
                'field' => 'sale_price',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->sale_price, 2, ',', '.'),
            ],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
