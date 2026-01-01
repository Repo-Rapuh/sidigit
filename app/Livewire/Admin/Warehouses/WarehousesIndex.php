<?php

namespace App\Livewire\Admin\Warehouses;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Gudang')]
class WarehousesIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('warehouse.view');
        $this->setPageMeta(
            'Daftar Gudang',
            'Kelola lokasi dan informasi gudang.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Gudang', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.warehouses.index');
    }
}
