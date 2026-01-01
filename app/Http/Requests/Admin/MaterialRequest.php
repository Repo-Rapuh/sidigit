<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'category_id' => ['required', 'exists:mst_categories,id'],
            'unit_id' => ['required', 'exists:mst_units,id'],
            'description' => ['nullable', 'string'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Material',
            'category_id' => 'Kategori',
            'unit_id' => 'Satuan',
            'description' => 'Deskripsi',
            'reorder_level' => 'Batas Minimum',
        ];
    }
}
