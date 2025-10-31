<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Services\SupplierService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SupplierController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    public function __construct(SupplierService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('supplier.view');

        $suppliers = $this->service->getPaginated();

        return view('admin.supplier.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('supplier.create');

        return view('admin.supplier.create');
    }

    public function store(SupplierRequest $request)
    {
        $this->authorize('supplier.create');

        try {
            $supplier = $this->service->store($request->validated());

            return redirect()
                ->route('suppliers.index')
                ->with('success', "Supplier {$supplier->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan supplier. Silakan coba lagi.');
        }
    }

    public function edit(int $supplier)
    {
        $this->authorize('supplier.edit');

        try {
            $supplier = $this->service->find($supplier);

            return view('admin.supplier.edit', compact('supplier'));
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('suppliers.index')
                ->with('error', 'Data supplier tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(SupplierRequest $request, int $supplier)
    {
        $this->authorize('supplier.edit');

        try {
            $supplierModel = $this->service->update($supplier, $request->validated());

            return redirect()
                ->route('suppliers.index')
                ->with('success', "Supplier {$supplierModel->name} berhasil diperbarui.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data supplier. Silakan coba lagi.');
        }
    }

    public function destroy(int $supplier)
    {
        $this->authorize('supplier.delete');

        try {
            $supplierModel = $this->service->find($supplier);
            $this->service->destroy($supplier);

            return redirect()
                ->route('suppliers.index')
                ->with('success', "Supplier {$supplierModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('suppliers.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data supplier. Silakan coba lagi.');
        }
    }
}
