<?php

namespace App\Livewire\Forms;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use Livewire\Form;

class WarehouseForm extends Form
{
    public ?int $id = null;

    public string $name = '';
    public ?string $location_lat = null;
    public ?string $location_lng = null;
    public ?string $description = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function fillFromModel(Warehouse $warehouse): void
    {
        $this->id = $warehouse->id;
        $this->name = $warehouse->name;
        $this->location_lat = $warehouse->location_lat !== null ? (string) $warehouse->location_lat : null;
        $this->location_lng = $warehouse->location_lng !== null ? (string) $warehouse->location_lng : null;
        $this->description = $warehouse->description;
    }

    public function store(WarehouseService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(WarehouseService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'description' => $this->description,
        ];
    }
}
