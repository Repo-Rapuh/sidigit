<?php

namespace App\Livewire\Admin\Accounting\Accounts;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Chart of Accounts')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('account.view');

        $this->setPageMeta(
            'Chart of Accounts',
            'Kelola daftar akun akuntansi per cabang.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Akuntansi', 'current' => true],
                ['label' => 'Chart of Accounts', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.accounting.accounts.index');
    }
}

