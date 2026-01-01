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
#[Title('Edit Pelanggan')]
class CustomersEdit extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public CustomerForm $form;
    public array $memberTypes = [];
    public int $customerId;

    protected CustomerService $service;

    public function boot(CustomerService $service): void
    {
        $this->service = $service;
    }

    public function mount(int $customer): void
    {
        $this->authorize('customer.edit');
        $this->customerId = $customer;
        $this->memberTypes = $this->service->memberTypes();

        $customerModel = $this->service->find($customer);
        $this->form->fillFromModel($customerModel);

        $this->setPageMeta(
            'Edit Pelanggan',
            'Perbarui informasi pelanggan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Pelanggan', 'url' => route('customers.index')],
                ['label' => 'Edit', 'current' => true],
            ]
        );
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            session()->flash('toast', ['message' => 'Pelanggan berhasil diperbarui.', 'type' => 'success']);
            $this->redirectRoute('customers.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui pelanggan.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.customers.edit');
    }
}
