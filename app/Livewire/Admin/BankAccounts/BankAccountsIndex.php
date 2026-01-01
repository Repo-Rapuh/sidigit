<?php

namespace App\Livewire\Admin\BankAccounts;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Rekening Bank')]
class BankAccountsIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('bank-account.view');
        $this->setPageMeta(
            'Daftar Rekening Bank',
            'Kelola data rekening bank untuk transaksi.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Rekening Bank', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.bank-accounts.index');
    }
}
