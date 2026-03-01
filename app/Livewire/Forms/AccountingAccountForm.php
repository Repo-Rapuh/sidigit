<?php

namespace App\Livewire\Forms;

use App\Models\AccountingAccount;
use App\Services\AccountingAccountService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AccountingAccountForm extends Form
{
    public ?int $id = null;

    #[Validate('required|string|max:32')]
    public string $code = '';

    #[Validate('required|string|min:3|max:160')]
    public string $name = '';

    #[Validate('required|string|in:asset,liability,equity,revenue,expense')]
    public string $type = 'asset';

    #[Validate('required|string|in:debit,credit')]
    public string $normal_balance = 'debit';

    #[Validate('boolean')]
    public bool $is_active = true;

    #[Validate('nullable|string|max:500')]
    public ?string $notes = null;

    protected function messages(): array
    {
        return [
            'code.required' => 'Kode akun wajib diisi.',
            'code.max' => 'Kode akun maksimal 32 karakter.',
            'name.required' => 'Nama akun wajib diisi.',
            'name.min' => 'Nama akun minimal 3 karakter.',
            'name.max' => 'Nama akun maksimal 160 karakter.',
            'type.required' => 'Tipe akun wajib dipilih.',
            'type.in' => 'Tipe akun tidak valid.',
            'normal_balance.required' => 'Saldo normal wajib dipilih.',
            'normal_balance.in' => 'Saldo normal tidak valid.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }

    public function fillFromModel(AccountingAccount $account): void
    {
        $this->id = $account->id;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->normal_balance = $account->normal_balance;
        $this->is_active = (bool) $account->is_active;
        $this->notes = $account->notes;
    }

    public function store(AccountingAccountService $service): void
    {
        $this->validate();

        $service->store([
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'normal_balance' => $this->normal_balance,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ]);
    }

    public function update(AccountingAccountService $service): void
    {
        $this->validate();

        $service->update($this->id, [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'normal_balance' => $this->normal_balance,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ]);
    }
}

