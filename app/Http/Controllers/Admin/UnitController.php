<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UnitRequest;
use App\Services\UnitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UnitController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    public function __construct(UnitService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('unit.view');

        $units = $this->service->getPaginated();

        return view('admin.unit.index', compact('units'));
    }

    public function create()
    {
        $this->authorize('unit.create');

        return view('admin.unit.create');
    }

    public function store(UnitRequest $request)
    {
        $this->authorize('unit.create');

        try {
            $unit = $this->service->store($request->validated());

            return redirect()
                ->route('units.index')
                ->with('success', "Satuan {$unit->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan satuan. Silakan coba lagi.');
        }
    }

    public function edit(int $unit)
    {
        $this->authorize('unit.edit');

        try {
            $unit = $this->service->find($unit);

            return view('admin.unit.edit', compact('unit'));
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('units.index')
                ->with('error', 'Data satuan tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(UnitRequest $request, int $unit)
    {
        $this->authorize('unit.edit');

        try {
            $unitModel = $this->service->update($unit, $request->validated());

            return redirect()
                ->route('units.index')
                ->with('success', "Satuan {$unitModel->name} berhasil diperbarui.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data satuan. Silakan coba lagi.');
        }
    }

    public function destroy(int $unit)
    {
        $this->authorize('unit.delete');

        try {
            $unitModel = $this->service->find($unit);
            $this->service->destroy($unit);

            return redirect()
                ->route('units.index')
                ->with('success', "Satuan {$unitModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('units.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data satuan. Silakan coba lagi.');
        }
    }
}
