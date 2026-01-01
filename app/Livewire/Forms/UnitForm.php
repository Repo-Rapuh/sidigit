<?php

namespace App\Livewire\Forms;

use App\Models\Unit;
use App\Services\UnitService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UnitForm extends Form
{
    public ?int $id = null;

    #[Validate('required|string|min:3')]
    public string $name = '';

    public function fillFromModel(Unit $unit): void
    {
        $this->id = $unit->id;
        $this->name = $unit->name;
    }

    public function store(UnitService $service): void
    {
        $this->validate();

        $service->store(['name' => $this->name]);
    }

    public function update(UnitService $service): void
    {
        $this->validate();

        $service->update($this->id, ['name' => $this->name]);
    }
}
