<?php

namespace App\Livewire\Forms;

use App\Models\Material;
use App\Services\MaterialService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MaterialForm extends Form
{
    public ?int $id = null;

    #[Validate('required|string|max:128')]
    public string $name = '';

    #[Validate('required|integer|exists:mst_categories,id')]
    public ?int $category_id = null;

    #[Validate('required|integer|exists:mst_units,id')]
    public ?int $unit_id = null;

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $reorder_level = null;

    public function fillFromModel(Material $material): void
    {
        $this->id = $material->id;
        $this->name = $material->name;
        $this->category_id = $material->category_id;
        $this->unit_id = $material->unit_id;
        $this->description = $material->description;
        $this->reorder_level = $material->reorder_level !== null
            ? (string) $material->reorder_level
            : null;
    }

    public function store(MaterialService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(MaterialService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'description' => $this->description,
            'reorder_level' => $this->reorder_level,
        ];
    }
}
