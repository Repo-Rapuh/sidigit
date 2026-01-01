<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Audit Log')]
class AuditLogsIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('audit-log.view');
        $this->setPageMeta(
            'Audit Log',
            'Rekam jejak aktivitas penting yang terjadi di dalam sistem.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Audit Log', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.audit-logs.index');
    }
}
