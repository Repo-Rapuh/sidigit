<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('category.view');

        $categories = $this->service->getPaginated();

        return view('admin.category.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('category.create');

        return view('admin.category.create');
    }

    public function store(CategoryRequest $request)
    {
        $this->authorize('category.create');

        try {
            $category = $this->service->store($request->validated());

            return redirect()
                ->route('categories.index')
                ->with('success', "Kategori {$category->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan kategori. Silakan coba lagi.');
        }
    }

    public function edit(int $category)
    {
        $this->authorize('category.edit');

        try {
            $category = $this->service->find($category);

            return view('admin.category.edit', compact('category'));
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('categories.index')
                ->with('error', 'Data kategori tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(CategoryRequest $request, int $category)
    {
        $this->authorize('category.edit');

        try {
            $categoryModel = $this->service->update($category, $request->validated());

            return redirect()
                ->route('categories.index')
                ->with('success', "Kategori {$categoryModel->name} berhasil diperbarui.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data kategori. Silakan coba lagi.');
        }
    }

    public function destroy(int $category)
    {
        $this->authorize('category.delete');

        try {
            $categoryModel = $this->service->find($category);
            $this->service->destroy($category);

            return redirect()
                ->route('categories.index')
                ->with('success', "Kategori {$categoryModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('categories.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data kategori. Silakan coba lagi.');
        }
    }
}
