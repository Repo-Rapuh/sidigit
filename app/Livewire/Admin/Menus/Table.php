<?php

namespace App\Livewire\Admin\Menus;

use App\Livewire\BaseTable;
use App\Livewire\Forms\MenuForm;
use App\Services\MenuService;
use Illuminate\Support\Str;
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
        return $this->service->parentOptions($this->activeId);
    }

    public function getIconOptionsProperty(): array
    {
        return collect(config('menu.icons', []))
            ->keys()
            ->reject(fn($key) => $key === 'default')
            ->reject(fn($key) => Str::startsWith((string) $key, 'bi bi-'))
            ->map(fn($key) => [
                'id' => (string) $key,
                'label' => (string) $key,
            ])
            ->values()
            ->toArray();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
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
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat menu.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Menu berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui menu.');
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
            $this->toastError($e, 'Gagal menghapus menu.');
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
            $this->toastError($e, 'Gagal menghapus menu terpilih.');
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
