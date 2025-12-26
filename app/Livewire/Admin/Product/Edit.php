<?php

namespace App\Livewire\Admin\Product;

use App\Livewire\Admin\Product\Concerns\HandlesProductForm;
use App\Services\ProductService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Produk')]
class Edit extends Component
{
    use AuthorizesRequests;
    use HandlesProductForm;
    use WithPageMeta;

    public int $productId;
    public string $sku = '';
    public string $name = '';
    public $base_price = null;
    public $sale_price = null;
    public $length_cm = null;
    public $width_cm = null;
    public ?int $unit_id = null;
    public ?int $category_id = null;
    public ?string $product_description = null;
    public array $materials = [];

    protected ProductService $service;

    protected array $messages = [
        'materials.required' => 'Pilih minimal satu material untuk produk ini.',
        'materials.min' => 'Pilih minimal satu material untuk produk ini.',
    ];

    protected array $validationAttributes = [
        'sku' => 'SKU',
        'name' => 'Nama Produk',
        'base_price' => 'Harga Pokok',
        'sale_price' => 'Harga Jual',
        'length_cm' => 'Panjang (cm)',
        'width_cm' => 'Lebar (cm)',
        'unit_id' => 'Satuan',
        'category_id' => 'Kategori Produk',
        'product_description' => 'Deskripsi',
        'materials' => 'Material Produk',
        'materials.*' => 'Material Produk',
    ];

    public function mount(int $product, ProductService $service): void
    {
        $this->authorize('product.edit');

        $this->service = $service;

        $this->loadReferenceData();

        try {
            $productModel = $this->service->find($product);
        } catch (\Throwable $th) {
            report($th);

            session()->flash('toast', [
                'message' => 'Data produk tidak ditemukan atau tidak dapat diakses.',
                'type' => 'error',
            ]);
            $this->redirectRoute('products.index', navigate: true);

            return;
        }

        $this->productId = $productModel->id;
        $this->sku = (string) $productModel->sku;
        $this->name = (string) $productModel->name;
        $this->base_price = $productModel->base_price;
        $this->sale_price = $productModel->sale_price;
        $this->length_cm = $productModel->length_cm ?: null;
        $this->width_cm = $productModel->width_cm ?: null;
        $this->unit_id = $productModel->unit_id;
        $this->category_id = $productModel->category_id;
        $this->product_description = $productModel->description;
        $this->materials = $productModel->productMaterials
            ->pluck('material_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $this->refreshMaterialsForCategory($this->category_id);
        $this->syncDimensionFields();

        $this->setPageMeta(
            'Edit Produk',
            'Perbarui informasi produk, harga, dan material yang digunakan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produk', 'url' => route('products.index')],
                ['label' => 'Edit', 'current' => true],
            ]
        );
    }

    public function updatedCategoryId($value): void
    {
        $this->materials = [];
        $this->refreshMaterialsForCategory($value ? (int) $value : null);
    }

    public function updatedUnitId($value): void
    {
        $this->syncDimensionFields(true);
    }

    protected function rules(): array
    {
        return $this->rulesFor($this->productId ?? null);
    }

    protected function rulesFor(?int $productId): array
    {
        $categoryId = $this->category_id;

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
            'product_description' => ['nullable', 'string'],
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

    public function update(): void
    {
        $data = $this->validate();
        $data['description'] = $data['product_description'] ?? null;
        unset($data['product_description']);

        try {
            $product = $this->service->update($this->productId, $data);

            session()->flash('toast', [
                'message' => "Produk {$product->name} berhasil diperbarui.",
                'type' => 'success',
            ]);
            $this->redirectRoute('products.index', navigate: true);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            report($th);

            $this->dispatch('toast', message: 'Terjadi kesalahan saat memperbarui data produk. Silakan coba lagi.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.product.edit');
    }
}
