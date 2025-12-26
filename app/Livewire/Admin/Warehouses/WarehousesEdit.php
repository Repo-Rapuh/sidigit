<?php

namespace App\Livewire\Admin\Warehouses;

use App\Livewire\Forms\WarehouseForm;
use App\Services\WarehouseService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Gudang')]
class WarehousesEdit extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public WarehouseForm $form;
    public int $warehouseId;

    protected WarehouseService $service;

    public function boot(WarehouseService $service): void
    {
        $this->service = $service;
    }

    public function mount(int $warehouse): void
    {
        $this->authorize('warehouse.edit');
        $this->warehouseId = $warehouse;

        $warehouseModel = $this->service->find($warehouse);
        $this->form->fillFromModel($warehouseModel);

        $this->setPageMeta(
            'Edit Gudang',
            'Perbarui informasi gudang.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Gudang', 'url' => route('warehouses.index')],
                ['label' => 'Edit', 'current' => true],
            ]
        );
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            session()->flash('toast', ['message' => 'Gudang berhasil diperbarui.', 'type' => 'success']);
            $this->redirectRoute('warehouses.index', navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui gudang.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.warehouses.edit');
    }
}
