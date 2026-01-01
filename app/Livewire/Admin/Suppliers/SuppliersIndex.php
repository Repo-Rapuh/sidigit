<?php

namespace App\Livewire\Admin\Suppliers;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Supplier')]
class SuppliersIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('supplier.view');
        $this->setPageMeta(
            'Daftar Supplier',
            'Kelola data supplier dan kontaknya.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Supplier', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.suppliers.index');
    }
}
