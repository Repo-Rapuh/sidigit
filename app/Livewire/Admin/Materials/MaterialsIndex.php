<?php

namespace App\Livewire\Admin\Materials;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Material')]
class MaterialsIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('material.view');
        $this->setPageMeta(
            'Daftar Material',
            'Kelola informasi material untuk kebutuhan produksi.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Material', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.materials.index');
    }
}
