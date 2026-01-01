<?php

namespace App\Livewire\Admin\Menus;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Menu')]
class MenusIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('menu.view');
        $this->setPageMeta(
            'Daftar Menu',
            'Atur struktur navigasi aplikasi dan urutan tampil.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Menu', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.menus.index');
    }
}
