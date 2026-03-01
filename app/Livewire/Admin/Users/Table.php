<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\BaseTable;
use App\Livewire\Forms\UserForm;
use App\Services\UserService;
use App\Services\RoleService;
use App\Services\BranchService;
use App\Services\EmployeeService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Models\User;

class Table extends BaseTable
{
    protected UserService $service;
    protected RoleService $roleService;
    protected BranchService $branchService;
    protected EmployeeService $employeeService;

    public UserForm $form;

    public function boot(UserService $service, RoleService $roleService, BranchService $branchService, EmployeeService $employeeService): void
    {
        $this->service = $service;
        $this->roleService = $roleService;
        $this->branchService = $branchService;
        $this->employeeService = $employeeService;
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['name', 'email', 'username']);
    }

    public function getAvailableRolesProperty()
    {
        $roles = $this->roleService->all();
        $actor = auth()->user();

        if ($actor && !$actor->hasRoleSlug('superadmin')) {
            return $roles->reject(fn ($role) => (string) ($role->slug ?? '') === 'owner')->values();
        }

        return $roles;
    }

    public function getAvailableBranchesProperty()
    {
        return $this->branchService->query()->orderBy('name')->get();
    }

    public function getAvailableEmployeesProperty()
    {
        $assigned = $this->service->query()->whereNotNull('employee_id')->pluck('employee_id')->all();
        $currentEmployeeId = $this->form->employee_id;
        if ($currentEmployeeId) {
            $assigned = array_values(array_filter($assigned, fn ($id) => (int) $id !== (int) $currentEmployeeId));
        }

        return $this->employeeService->query()
            ->when(!empty($assigned), fn ($query) => $query->whereNotIn('id', $assigned))
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                $label = $employee->email ? "{$employee->name} - {$employee->email}" : $employee->name;
                return ['id' => $employee->id, 'name' => $label];
            });
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->requirePassword = true;
        $this->form->without_employee = true;
        $this->form->employee_id = null;
        $defaultBranchId = auth()->user()?->branch_id
            ?? $this->branchService->query()->where('is_main', true)->value('id');
        $this->form->branch_id = $defaultBranchId;
        $this->form->branch_ids = $defaultBranchId ? [$defaultBranchId] : [];
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
        $user = $this->service->find($id);
        $user->load('roles', 'branches');
        $this->form->fillFromModel($user);
        $this->form->requirePassword = false;
    }

    public function updatedFormWithoutEmployee(bool $value): void
    {
        if ($value) {
            $this->form->employee_id = null;
        }
    }

    public function updatedFormEmployeeId(?int $value): void
    {
        if (empty($value)) {
            return;
        }

        $employee = $this->employeeService->find($value);
        $this->form->name = $employee->name ?? $this->form->name;
        $this->form->username = $this->suggestUsername($employee->name, $this->form->id);
        $this->form->email = $employee->email ?? '';
    }

    protected function suggestUsername(string $name, ?int $ignoreId): string
    {
        $base = Str::slug($name, '.');
        $base = $base !== '' ? $base : 'user';

        $username = $base;
        $suffix = 1;

        $exists = fn ($value) => User::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('username', $value)
            ->exists();

        while ($exists($username)) {
            $suffix++;
            $username = $base . $suffix;
        }

        return $username;
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'User berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat user.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'User berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui user.');
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
            $this->toastError($e, 'Gagal menghapus user.');
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
            $this->toastError($e, 'Gagal menghapus user terpilih.');
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
            ['label' => 'Trashed', 'method' => 'goTrashed', 'class' => 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700', 'icon' => 'archive'],
        ];
    }

    public function goTrashed(): void
    {
        $this->redirectRoute('users.trashed');
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
