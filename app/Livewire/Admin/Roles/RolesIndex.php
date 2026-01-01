<?php

namespace App\Livewire\Admin\Roles;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Role')]
class RolesIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('role.view');
        $this->setPageMeta(
            'Daftar Role',
            'Kontrol akses berdasarkan peran dan pastikan pengguna memiliki hak yang tepat.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Role', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.roles.index');
    }
}
