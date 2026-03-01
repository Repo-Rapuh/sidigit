<?php

namespace App\Livewire\Admin\Productions;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Riwayat Produksi')]
class HistoryIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('production.view');

        $this->setPageMeta(
            'Riwayat Produksi',
            'Daftar job produksi lengkap dengan jejak perubahan statusnya.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produksi', 'url' => route('productions.index')],
                ['label' => 'Riwayat', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.productions.history');
    }
}
