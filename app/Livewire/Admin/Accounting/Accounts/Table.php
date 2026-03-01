<?php

namespace App\Livewire\Admin\Accounting\Accounts;

use App\Livewire\BaseTable;
use App\Livewire\Forms\AccountingAccountForm;
use App\Services\AccountingAccountService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected AccountingAccountService $service;

    public AccountingAccountForm $form;

    public array $accountTypeOptions = [];

    public function boot(AccountingAccountService $service): void
    {
        $this->service = $service;
        $this->accountTypeOptions = $service->accountTypeOptions();
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['code', 'name', 'type']);
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->type = 'asset';
        $this->form->normal_balance = 'debit';
        $this->form->is_active = true;
    }

    protected function loadForm(int $id): void
    {
        $account = $this->service->find($id);
        $this->form->fillFromModel($account);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Akun berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat akun.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Akun berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui akun.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Akun berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus akun.');
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
            $this->dispatch('toast', message: 'Akun terpilih berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus akun terpilih.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.accounting.accounts.form';
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
            ['label' => 'Tambah Akun', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Kode', 'field' => 'code', 'sortable' => true],
            ['label' => 'Nama Akun', 'field' => 'name', 'sortable' => true],
            ['label' => 'Tipe', 'field' => 'type', 'sortable' => true],
            ['label' => 'Saldo Normal', 'field' => 'normal_balance', 'sortable' => false],
            [
                'label' => 'Status',
                'field' => 'is_active',
                'sortable' => false,
                'format' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif',
            ],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function createModalWidth(): string
    {
        return '3xl';
    }

    protected function editModalWidth(): string
    {
        return '3xl';
    }

    protected function deleteModalWidth(): string
    {
        return '3xl';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Akun COA';
    }

    protected function editModalTitle(): string
    {
        return 'Edit Akun COA';
    }
}

