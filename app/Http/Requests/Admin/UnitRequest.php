<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->route('unit');

        return [
            'name' => [
                'required',
                'string',
                'max:64',
                Rule::unique('mst_units', 'name')->ignore($unitId),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Satuan',
        ];
    }
}
