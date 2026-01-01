<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarehouseRequest;
use App\Services\WarehouseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WarehouseController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    public function __construct(WarehouseService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('warehouse.view');

        $warehouses = $this->service->getPaginated();

        return view('admin.warehouse.index', compact('warehouses'));
    }

    public function create()
    {
        $this->authorize('warehouse.create');

        return view('admin.warehouse.create');
    }

    public function store(WarehouseRequest $request)
    {
        $this->authorize('warehouse.create');

        try {
            $warehouse = $this->service->store($request->validated());

            return redirect()
                ->route('warehouses.index')
                ->with('success', "Gudang {$warehouse->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan gudang. Silakan coba lagi.');
        }
    }

    public function edit(int $warehouse)
    {
        $this->authorize('warehouse.edit');

        try {
            $warehouse = $this->service->find($warehouse);

            return view('admin.warehouse.edit', compact('warehouse'));
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('warehouses.index')
                ->with('error', 'Data gudang tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(WarehouseRequest $request, int $warehouse)
    {
        $this->authorize('warehouse.edit');

        try {
            $warehouseModel = $this->service->update($warehouse, $request->validated());

            return redirect()
                ->route('warehouses.index')
                ->with('success', "Gudang {$warehouseModel->name} berhasil diperbarui.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data gudang. Silakan coba lagi.');
        }
    }

    public function destroy(int $warehouse)
    {
        $this->authorize('warehouse.delete');

        try {
            $warehouseModel = $this->service->find($warehouse);
            $this->service->destroy($warehouse);

            return redirect()
                ->route('warehouses.index')
                ->with('success', "Gudang {$warehouseModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('warehouses.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data gudang. Silakan coba lagi.');
        }
    }
}
