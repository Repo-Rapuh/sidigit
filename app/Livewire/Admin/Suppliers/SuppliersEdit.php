<?php

namespace App\Livewire\Admin\Suppliers;

use App\Livewire\Forms\SupplierForm;
use App\Services\SupplierService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Supplier')]
class SuppliersEdit extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public SupplierForm $form;
    public int $supplierId;

    protected SupplierService $service;

    public function boot(SupplierService $service): void
    {
        $this->service = $service;
    }

    public function mount(int $supplier): void
    {
        $this->authorize('supplier.edit');
        $this->supplierId = $supplier;

        $supplierModel = $this->service->find($supplier);
        $this->form->fillFromModel($supplierModel);

        $this->setPageMeta(
            'Edit Supplier',
            'Perbarui informasi supplier.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Supplier', 'url' => route('suppliers.index')],
                ['label' => 'Edit', 'current' => true],
            ]
        );
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            session()->flash('toast', ['message' => 'Supplier berhasil diperbarui.', 'type' => 'success']);
            $this->redirectRoute('suppliers.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui supplier.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.suppliers.edit');
    }
}
