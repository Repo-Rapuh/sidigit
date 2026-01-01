<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\BaseTable;
use App\Livewire\Forms\UserForm;
use App\Models\Role;
use App\Services\UserService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected UserService $service;

    public UserForm $form;

    public function boot(UserService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['name', 'email', 'username']);
    }

    public function getAvailableRolesProperty()
    {
        return Role::orderBy('name')->get();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->requirePassword = true;
    }

    protected function loadForm(int $id): void
    {
        $user = $this->service->find($id);
        $user->load('roles');
        $this->form->fillFromModel($user);
        $this->form->requirePassword = false;
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'User berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal membuat user.', type: 'error');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'User berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui user.', type: 'error');
        }
    }

    public function delete(): void
    {
        if (auth()->id() === $this->activeId) {
            $this->dispatch('toast', message: 'Anda tidak dapat menghapus akun Anda sendiri.', type: 'warning');
            return;
        }

        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'User berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus user.', type: 'error');
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Pilih minimal 1 data.', type: 'warning');
            return;
        }

        $currentId = auth()->id();
        $ids = array_values(array_filter($this->selected, fn($id) => (int) $id !== (int) $currentId));

        if (empty($ids)) {
            $this->dispatch('toast', message: 'Tidak ada user lain yang bisa dihapus.', type: 'warning');
            return;
        }

        try {
            $this->service->destroyMany($ids);
            $this->selected = [];
            $this->selectAll = false;
            $this->closeModal();
            $this->dispatch('toast', message: 'User terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus user terpilih.', type: 'error');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.users.form';
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
            ['label' => 'Tambah User', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Username', 'field' => 'username', 'sortable' => true],
            ['label' => 'Email', 'field' => 'email', 'sortable' => true],
            [
                'label' => 'Role',
                'field' => 'roles',
                'sortable' => false,
                'format' => function ($row) {
                    $roles = $row->roles->pluck('name')->join(', ');
                    return $roles !== '' ? $roles : '-';
                },
            ],
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
        return 'Tambah User';
    }

    protected function editModalTitle(): string
    {
        return 'Edit User';
    }
}
