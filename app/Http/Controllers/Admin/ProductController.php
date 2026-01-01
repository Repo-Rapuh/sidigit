<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Material;
use App\Models\Unit;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('product.view');

        $products = $this->service->getPaginated();

        return view('admin.product.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('product.create');

        $categories = Category::orderBy('name')->get();
        $materialsByCategory = $this->getMaterialsByCategory();
        $units = Unit::orderBy('name')->get();

        return view('admin.product.create', compact('categories', 'materialsByCategory', 'units'));
    }

    public function store(ProductRequest $request)
    {
        $this->authorize('product.create');

        try {
            $product = $this->service->store($request->validated());

            return redirect()
                ->route('products.index')
                ->with('success', "Produk {$product->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan produk. Silakan coba lagi.');
        }
    }

    public function edit(int $product)
    {
        $this->authorize('product.edit');

        try {
            $productModel = $this->service->find($product);
            $categories = Category::orderBy('name')->get();
            $materialsByCategory = $this->getMaterialsByCategory();
            $units = Unit::orderBy('name')->get();

            return view('admin.product.edit', [
                'product' => $productModel,
                'categories' => $categories,
                'materialsByCategory' => $materialsByCategory,
                'units' => $units,
            ]);
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('products.index')
                ->with('error', 'Data produk tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(ProductRequest $request, int $product)
    {
        $this->authorize('product.edit');

        try {
            $updatedProduct = $this->service->update($product, $request->validated());

            return redirect()
                ->route('products.index')
                ->with('success', "Produk {$updatedProduct->name} berhasil diperbarui.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data produk. Silakan coba lagi.');
        }
    }

    public function destroy(int $product)
    {
        $this->authorize('product.delete');

        try {
            $productModel = $this->service->find($product);
            $this->service->destroy($product);

            return redirect()
                ->route('products.index')
                ->with('success', "Produk {$productModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('products.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data produk. Silakan coba lagi.');
        }
    }

    protected function getMaterialsByCategory(): array
    {
        return Material::query()
            ->with('unit')
            ->orderBy('name')
            ->get()
            ->groupBy('category_id')
            ->mapWithKeys(function ($materials, $categoryId) {
                return [
                    (string) $categoryId => $materials->map(function (Material $material) {
                        return [
                            'id' => (string) $material->id,
                            'name' => $material->name,
                            'unit' => optional($material->unit)->name,
                        ];
                    })->values(),
                ];
            })
            ->toArray();
    }
}
