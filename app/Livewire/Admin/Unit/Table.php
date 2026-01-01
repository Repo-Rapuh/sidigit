<?php

namespace App\Livewire\Admin\Unit;

use App\Livewire\BaseTable;
use App\Livewire\Forms\UnitForm;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected UnitService $service;
    public UnitForm $form;

    public bool $showEditModal = false;
    public ?Unit $editingUnit = null;

    public string $editName = '';

    public function boot(UnitService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->service->query()->when(
            isset($this->search),
            fn($q) => $q->where('name', 'like', "%{$this->search}%")
        );
    }

    protected function resetForm(): void
    {
        $this->form->reset();
    }

    protected function loadForm(int $id): void
    {
        $unit = $this->service->find($id);
        $this->form->fillFromModel($unit);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);

            $this->closeModal();
            $this->dispatch('toast', message: 'Unit berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal membuat unit.', type: 'error');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);

            $this->closeModal();
            $this->dispatch('toast', message: 'Unit berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui unit.', type: 'error');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);

            $this->closeModal();
            $this->dispatch('toast', message: 'Unit berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus unit.', type: 'error');
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
            $this->dispatch('toast', message: 'Unit terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus unit terpilih.', type: 'error');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.unit.form';
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
            ['label' => 'Tambah Unit', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Name', 'field' => 'name', 'sortable' => true],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    /* ===================== WIDTH HOOKS ===================== */

    protected function createModalWidth(): string
    {
        return 'md';
    }

    protected function editModalWidth(): string
    {
        return 'md';
    }

    protected function deleteModalWidth(): string
    {
        return 'lg';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Unit';
    }

    protected function editModalTitle(): string
    {
        return 'Update Unit';
    }
}
