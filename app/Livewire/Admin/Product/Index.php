<?php

namespace App\Livewire\Admin\Product;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Produk')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('product.view');
        $this->setPageMeta(
            'Daftar Produk',
            'Kelola produk beserta harga dan materialnya.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produk', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.product.index');
    }
}
