<?php

namespace App\Livewire\Admin\Menus;

use App\Helpers\IconHelper;
use App\Livewire\BaseTable;
use App\Livewire\Forms\MenuForm;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected MenuService $service;

    public MenuForm $form;

    public function boot(MenuService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['name', 'route_name']);
    }

    public function getParentMenuOptionsProperty()
    {
        return Menu::query()
            ->when($this->activeId, fn($q) => $q->where('id', '!=', $this->activeId))
            ->orderBy('name')
            ->get();
    }

    public function getIconOptionsProperty(): array
    {
        return IconHelper::getIcons();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
    }

    protected function loadForm(int $id): void
    {
        $menu = $this->service->find($id);
        $this->form->fillFromModel($menu);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Menu berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal membuat menu.', type: 'error');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Menu berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui menu.', type: 'error');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Menu berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus menu.', type: 'error');
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
            $this->dispatch('toast', message: 'Menu terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus menu terpilih.', type: 'error');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.menus.form';
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
            ['label' => 'Tambah Menu', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Parent', 'field' => 'parent.name', 'sortable' => false],
            ['label' => 'Route', 'field' => 'route_name', 'sortable' => false],
            ['label' => 'Urutan', 'field' => 'order', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function createModalWidth(): string
    {
        return '2xl';
    }

    protected function editModalWidth(): string
    {
        return '2xl';
    }

    protected function deleteModalWidth(): string
    {
        return '2xl';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Menu';
    }

    protected function editModalTitle(): string
    {
        return 'Edit Menu';
    }
}
