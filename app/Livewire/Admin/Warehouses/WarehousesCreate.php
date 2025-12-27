<?php

namespace App\Livewire\Admin\Warehouses;

use App\Livewire\Forms\WarehouseForm;
use App\Services\WarehouseService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Gudang')]
class WarehousesCreate extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public WarehouseForm $form;

    protected WarehouseService $service;

    public function boot(WarehouseService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('warehouse.create');
        $this->setPageMeta(
            'Tambah Gudang',
            'Lengkapi informasi gudang baru.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Gudang', 'url' => route('warehouses.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );
    }

    public function save(): void
    {
        try {
            $this->form->store($this->service);
            session()->flash('toast', ['message' => 'Gudang berhasil ditambahkan.', 'type' => 'success']);
            $this->redirectRoute('warehouses.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menambahkan gudang.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.warehouses.create');
    }
}
