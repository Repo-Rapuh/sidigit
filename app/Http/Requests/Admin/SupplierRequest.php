<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'on_behalf' => ['nullable', 'string', 'max:128'],
            'address' => ['nullable', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:128'],
            'phone_number' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:128'],
            'rekening_number' => ['nullable', 'string', 'max:128'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Supplier',
            'on_behalf' => 'Atas Nama',
            'address' => 'Alamat',
            'industry' => 'Industri',
            'phone_number' => 'Nomor Telepon',
            'email' => 'Email',
            'rekening_number' => 'Nomor Rekening',
        ];
    }
}
