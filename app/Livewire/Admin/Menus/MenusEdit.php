<?php

namespace App\Livewire\Admin\Menus;

use App\Helpers\IconHelper;
use App\Models\Menu;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Menu')]
class MenusEdit extends Component
{
    use AuthorizesRequests;

    public Menu $menu;
    public string $name = '';
    public ?int $parent_id = null;
    public ?string $route_name = null;
    public ?string $icon = null;
    public int $order;

    public function mount(Menu $menu): void
    {
        $this->authorize('menu.edit');

        $this->menu = $menu;

        $this->name = $menu->name;
        $this->parent_id = $menu->parent_id;
        $this->route_name = $menu->route_name;
        $this->icon = $menu->icon;
        $this->order = $menu->order;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255', Rule::unique(Menu::class, 'route_name')->ignore($this->menu->id)],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        $this->menu->update($data);

        session()->flash('success', 'Menu berhasil diperbarui.');

        $this->redirectRoute('menus.index');
    }

    public function render()
    {
        return view('livewire.admin.menus.edit', [
            'parentMenuOptions' => $this->parentMenuOptions(),
            'icons' => IconHelper::getIcons(),
        ])->layoutData([
            'title' => 'Edit Menu',
        ]);
    }

    protected function parentMenuOptions(): array
    {
        return Menu::where('id', '!=', $this->menu->id)
            ->orderBy('name')
            ->get()
            ->map(fn($menu) => [
                'id' => $menu->id,
                'label' => $menu->name,
            ])
            ->toArray();
    }
}
