<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bankAccountId = $this->route('bank_account') ?? $this->route('bank-account');

        return [
            'rekening_number' => [
                'required',
                'string',
                'max:18',
                Rule::unique('mst_bank_accounts', 'rekening_number')->ignore($bankAccountId),
            ],
            'account_name' => ['required', 'string', 'max:64'],
            'bank_name' => ['required', 'string', 'max:64'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rekening_number' => 'Nomor Rekening',
            'account_name' => 'Nama Pemilik Rekening',
            'bank_name' => 'Nama Bank',
        ];
    }
}
