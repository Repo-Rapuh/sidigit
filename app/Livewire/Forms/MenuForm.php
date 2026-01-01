<?php

namespace App\Livewire\Forms;

use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Validation\Rule;
use Livewire\Form;

class MenuForm extends Form
{
    public ?int $id = null;
    public string $name = '';
    public ?int $parent_id = null;
    public ?string $route_name = null;
    public ?string $icon = null;
    public int $order = 0;

    protected function rules(): array
    {
        $uniqueRoute = Rule::unique(Menu::class, 'route_name');
        if (! empty($this->id)) {
            $uniqueRoute = $uniqueRoute->ignore($this->id);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255', $uniqueRoute],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ];
    }

    public function fillFromModel(Menu $menu): void
    {
        $this->id = $menu->id;
        $this->name = $menu->name;
        $this->parent_id = $menu->parent_id;
        $this->route_name = $menu->route_name;
        $this->icon = $menu->icon;
        $this->order = $menu->order;
    }

    public function store(MenuService $service): void
    {
        $this->validate();

        $service->store([
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'route_name' => $this->route_name,
            'icon' => $this->icon,
            'order' => $this->order,
        ]);
    }

    public function update(MenuService $service): void
    {
        $this->validate();

        $service->update($this->id, [
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'route_name' => $this->route_name,
            'icon' => $this->icon,
            'order' => $this->order,
        ]);
    }
}
