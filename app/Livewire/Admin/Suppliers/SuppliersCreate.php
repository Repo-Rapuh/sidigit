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
#[Title('Tambah Supplier')]
class SuppliersCreate extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public SupplierForm $form;

    protected SupplierService $service;

    public function boot(SupplierService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('supplier.create');
        $this->setPageMeta(
            'Tambah Supplier',
            'Lengkapi informasi supplier baru.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Supplier', 'url' => route('suppliers.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );
    }

    public function save(): void
    {
        try {
            $this->form->store($this->service);
            session()->flash('toast', ['message' => 'Supplier berhasil ditambahkan.', 'type' => 'success']);
            $this->redirectRoute('suppliers.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menambahkan supplier.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.suppliers.create');
    }
}
