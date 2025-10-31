<?php

namespace App\Http\Requests\Admin;

use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'name' => ['required', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:128'],
            'phone_number' => ['nullable', 'string', 'max:16'],
            'email' => [
                'nullable',
                'email',
                'max:64',
                Rule::unique('mst_employees', 'email')->ignore($employeeId),
            ],
            'photo' => ['nullable', 'image', 'max:2048'],
            'salary' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Karyawan',
            'address' => 'Alamat',
            'phone_number' => 'Nomor Telepon',
            'email' => 'Email',
            'photo' => 'Foto',
            'salary' => 'Gaji',
            'status' => 'Status',
        ];
    }
}
