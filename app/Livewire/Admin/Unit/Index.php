<?php

namespace App\Livewire\Admin\Unit;

use App\Services\UnitService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title(self::PAGE_TITLE)]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    private const PAGE_TITLE = 'Manajemen Satuan';

    protected UnitService $service;
    public function mount(UnitService $service)
    {
        $this->service = $service;
        $this->setPageMeta(
            title: self::PAGE_TITLE,
            description: 'Kelola daftar satuan yang digunakan dalam sistem.',
            breadcrumbs: [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Satuan', 'current' => true],
            ]
        );
    }

    public function render()
    {
        $this->authorize('unit.view');

        return view('livewire.admin.unit.index', [
            'units' => $this->service->getPaginated()
        ]);
    }
}
