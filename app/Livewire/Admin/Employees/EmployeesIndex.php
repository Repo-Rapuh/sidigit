<?php

namespace App\Livewire\Admin\Employees;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Karyawan')]
class EmployeesIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('employee.view');
        $this->setPageMeta(
            'Daftar Karyawan',
            'Kelola informasi karyawan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Karyawan', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.employees.index');
    }
}
