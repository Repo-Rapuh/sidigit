<?php

namespace App\Livewire\Admin\Materials;

use App\Livewire\BaseTable;
use App\Livewire\Forms\MaterialForm;
use App\Models\Category;
use App\Models\Unit;
use App\Services\MaterialService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected MaterialService $service;

    public MaterialForm $form;

    public function boot(MaterialService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['name', 'description']);
    }

    public function getCategoryOptionsProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function getUnitOptionsProperty()
    {
        return Unit::orderBy('name')->get();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
    }

    protected function loadForm(int $id): void
    {
        $material = $this->service->find($id);
        $this->form->fillFromModel($material);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Material berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal membuat material.', type: 'error');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Material berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui material.', type: 'error');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Material berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus material.', type: 'error');
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
            $this->dispatch('toast', message: 'Material terpilih berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus material terpilih.', type: 'error');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.materials.form';
    }

    protected function rowActions(): array
    {
        return [
            ['label' => 'Edit', 'method' => 'openEdit', 'class' => 'text-brand-500', 'icon' => 'pencil'],
            ['label' => 'Delete', 'method' => 'confirmDelete', 'class' => 'text-red-600', 'icon' => 'trash-2'],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => 'Tambah Material', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Material', 'field' => 'name', 'sortable' => true],
            ['label' => 'Kategori', 'field' => 'category.name', 'sortable' => false],
            ['label' => 'Satuan', 'field' => 'unit.name', 'sortable' => false],
            ['label' => 'Reorder Level', 'field' => 'reorder_level', 'sortable' => false],
            [
                'label' => 'Deskripsi',
                'field' => 'description',
                'sortable' => false,
                'format' => fn($row) => $row->description ? \Illuminate\Support\Str::limit($row->description, 60) : '–',
            ],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function createModalWidth(): string
    {
        return '5xl';
    }

    protected function editModalWidth(): string
    {
        return '5xl';
    }

    protected function deleteModalWidth(): string
    {
        return 'xl';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Material';
    }

    protected function editModalTitle(): string
    {
        return 'Edit Material';
    }
}
