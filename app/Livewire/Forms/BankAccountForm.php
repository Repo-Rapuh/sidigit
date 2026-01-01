<?php

namespace App\Livewire\Forms;

use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BankAccountForm extends Form
{
    public ?int $id = null;

    public string $rekening_number = '';
    public string $account_name = '';
    public string $bank_name = '';

    public function rules(): array
    {
        return [
            'rekening_number' => [
                'required',
                'string',
                'max:18',
                Rule::unique('mst_bank_accounts', 'rekening_number')->ignore($this->id),
            ],
            'account_name' => ['required', 'string', 'max:64'],
            'bank_name' => ['required', 'string', 'max:64'],
        ];
    }

    public function fillFromModel(BankAccount $bankAccount): void
    {
        $this->id = $bankAccount->id;
        $this->rekening_number = $bankAccount->rekening_number;
        $this->account_name = $bankAccount->account_name;
        $this->bank_name = $bankAccount->bank_name;
    }

    public function store(BankAccountService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(BankAccountService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'rekening_number' => $this->rekening_number,
            'account_name' => $this->account_name,
            'bank_name' => $this->bank_name,
        ];
    }
}
