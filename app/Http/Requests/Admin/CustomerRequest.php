<?php

namespace App\Http\Requests\Admin;

use App\Enums\CustomerMemberType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:128'],
            'phone_number' => ['nullable', 'string', 'max:16'],
            'email' => [
                'nullable',
                'email',
                'max:64',
                Rule::unique('mst_customers', 'email')->ignore($customerId),
            ],
            'member_type' => ['required', Rule::enum(CustomerMemberType::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Pelanggan',
            'address' => 'Alamat',
            'phone_number' => 'Nomor Telepon',
            'email' => 'Email',
            'member_type' => 'Tipe Anggota',
        ];
    }
}
