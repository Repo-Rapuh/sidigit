<?php

namespace App\Livewire\Admin\Customers;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Pelanggan')]
class CustomersIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('customer.view');
        $this->setPageMeta(
            'Daftar Pelanggan',
            'Kelola informasi pelanggan dan tipe anggotanya.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Pelanggan', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.customers.index');
    }
}
