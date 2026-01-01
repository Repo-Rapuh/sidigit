<?php

namespace App\Livewire\Forms;

use App\Models\Supplier;
use App\Services\SupplierService;
use Livewire\Form;

class SupplierForm extends Form
{
    public ?int $id = null;

    public string $name = '';
    public ?string $on_behalf = null;
    public ?string $address = null;
    public string $industry = '';
    public string $phone_number = '';
    public ?string $email = null;
    public ?string $rekening_number = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'on_behalf' => ['nullable', 'string', 'max:128'],
            'address' => ['nullable', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:128'],
            'phone_number' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:128'],
            'rekening_number' => ['nullable', 'string', 'max:128'],
        ];
    }

    public function fillFromModel(Supplier $supplier): void
    {
        $this->id = $supplier->id;
        $this->name = $supplier->name;
        $this->on_behalf = $supplier->on_behalf;
        $this->address = $supplier->address;
        $this->industry = $supplier->industry;
        $this->phone_number = $supplier->phone_number;
        $this->email = $supplier->email;
        $this->rekening_number = $supplier->rekening_number;
    }

    public function store(SupplierService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(SupplierService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'on_behalf' => $this->on_behalf,
            'address' => $this->address,
            'industry' => $this->industry,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'rekening_number' => $this->rekening_number,
        ];
    }
}
