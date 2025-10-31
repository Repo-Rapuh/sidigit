<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmployeeRequest;
use App\Services\EmployeeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EmployeeController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('employee.view');
        $employees = $this->service->getPaginated();

        return view('admin.employee.index', compact('employees'));
    }

    public function create()
    {
        $this->authorize('employee.create');
        $statuses = $this->service->statuses();

        return view('admin.employee.create', compact('statuses'));
    }

    public function store(EmployeeRequest $request)
    {
        $this->authorize('employee.create');
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo');
        }

        try {
            $employee = $this->service->store($data);

            return redirect()
                ->route('employees.index')
                ->with('success', "Karyawan {$employee->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan karyawan. Silakan coba lagi.');
        }
    }

    public function edit(int $employee)
    {
        $this->authorize('employee.edit');

        try {
            $employee = $this->service->find($employee);
            $statuses = $this->service->statuses();

            return view('admin.employee.edit', compact('employee', 'statuses'));
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('employees.index')
                ->with('error', 'Data karyawan tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(EmployeeRequest $request, int $employee)
    {
        $this->authorize('employee.edit');
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo');
        }

        try {
            $employeeModel = $this->service->update($employee, $data);

            return redirect()
                ->route('employees.index')
                ->with('success', "Karyawan {$employeeModel->name} berhasil diperbarui.");
        } catch (\Throwable $th) {
            report($th);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data karyawan. Silakan coba lagi.');
        }
    }

    public function destroy(int $employee)
    {
        $this->authorize('employee.delete');

        try {
            $employeeModel = $this->service->find($employee);
            $this->service->destroy($employee);

            return redirect()
                ->route('employees.index')
                ->with('success', "Karyawan {$employeeModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            return redirect()
                ->route('employees.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data karyawan. Silakan coba lagi.');
        }
    }
}
