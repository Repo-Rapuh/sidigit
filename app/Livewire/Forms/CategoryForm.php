<?php

namespace App\Livewire\Forms;

use App\Models\Category;
use App\Services\CategoryService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CategoryForm extends Form
{
    public ?int $id = null;

    #[Validate('required|string|min:3|max:128')]
    public string $name = '';

    public function fillFromModel(Category $category): void
    {
        $this->id = $category->id;
        $this->name = $category->name;
    }

    public function store(CategoryService $service): void
    {
        $this->validate();

        $service->store(['name' => $this->name]);
    }

    public function update(CategoryService $service): void
    {
        $this->validate();

        $service->update($this->id, ['name' => $this->name]);
    }
}
