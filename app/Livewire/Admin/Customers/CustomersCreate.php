<?php

namespace App\Livewire\Admin\Customers;

use App\Livewire\Forms\CustomerForm;
use App\Services\CustomerService;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Pelanggan')]
class CustomersCreate extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public CustomerForm $form;
    public array $memberTypes = [];

    protected CustomerService $service;

    public function boot(CustomerService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('customer.create');
        $this->memberTypes = $this->service->memberTypes();
        $this->setPageMeta(
            'Tambah Pelanggan',
            'Lengkapi informasi pelanggan baru.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Pelanggan', 'url' => route('customers.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );
    }

    public function save(): void
    {
        try {
            $this->form->store($this->service);
            session()->flash('toast', ['message' => 'Pelanggan berhasil ditambahkan.', 'type' => 'success']);
            $this->redirectRoute('customers.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menambahkan pelanggan.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.customers.create');
    }
}
