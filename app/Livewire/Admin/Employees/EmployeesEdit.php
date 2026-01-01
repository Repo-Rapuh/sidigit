<?php

namespace App\Livewire\Admin\Employees;

use App\Livewire\Forms\EmployeeForm;
use App\Services\EmployeeService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

#[Layout('layouts.app')]
#[Title('Edit Karyawan')]
class EmployeesEdit extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;
    use WithFilePond;

    public EmployeeForm $form;
    public array $statuses = [];
    public int $employeeId;
    public ?string $currentPhoto = null;

    protected EmployeeService $service;

    public function boot(EmployeeService $service): void
    {
        $this->service = $service;
    }

    public function mount(int $employee): void
    {
        $this->authorize('employee.edit');
        $this->employeeId = $employee;
        $this->statuses = $this->service->statuses();

        $employeeModel = $this->service->find($employee);
        $this->form->fillFromModel($employeeModel);
        $this->currentPhoto = $employeeModel->photo;

        $this->setPageMeta(
            'Edit Karyawan',
            'Perbarui informasi karyawan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Karyawan', 'url' => route('employees.index')],
                ['label' => 'Edit', 'current' => true],
            ]
        );
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            session()->flash('toast', ['message' => 'Karyawan berhasil diperbarui.', 'type' => 'success']);
            $this->redirectRoute('employees.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui karyawan.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.employees.edit');
    }
}
