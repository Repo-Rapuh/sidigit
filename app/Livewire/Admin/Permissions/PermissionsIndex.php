<?php

namespace App\Livewire\Admin\Permissions;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Permission')]
class PermissionsIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('permission.view');
        $this->setPageMeta(
            'Daftar Permission',
            'Kelola izin tindakan yang terhubung ke menu.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Permission', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.permissions.index');
    }
}
