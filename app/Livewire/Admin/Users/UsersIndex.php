<?php

namespace App\Livewire\Admin\Users;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen User')]
class UsersIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('users.view');
        $this->setPageMeta(
            'Daftar User',
            'Kelola akun pengguna beserta perannya.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'User', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.users.index');
    }
}
