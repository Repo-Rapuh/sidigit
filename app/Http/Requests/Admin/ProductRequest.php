<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = (int) $this->route('product');
        $categoryId = $this->input('category_id');

        return [
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('mst_products', 'sku')->ignore($productId),
            ],
            'name' => ['required', 'string', 'max:128'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'unit_id' => ['required', 'exists:mst_units,id'],
            'category_id' => ['required', 'exists:mst_categories,id'],
            'description' => ['nullable', 'string'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*' => [
                'integer',
                Rule::exists('mst_materials', 'id')->where(function ($query) use ($categoryId) {
                    if ($categoryId) {
                        $query->where('category_id', $categoryId);
                    }
                }),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
            'name' => 'Nama Produk',
            'base_price' => 'Harga Pokok',
            'sale_price' => 'Harga Jual',
            'length_cm' => 'Panjang (cm)',
            'width_cm' => 'Lebar (cm)',
            'unit_id' => 'Satuan',
            'category_id' => 'Kategori Produk',
            'description' => 'Deskripsi',
            'materials' => 'Material Produk',
            'materials.*' => 'Material Produk',
        ];
    }

    public function messages(): array
    {
        return [
            'materials.required' => 'Pilih minimal satu material untuk produk ini.',
            'materials.min' => 'Pilih minimal satu material untuk produk ini.',
        ];
    }
}
