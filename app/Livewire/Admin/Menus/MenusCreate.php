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
#[Title('Tambah Menu')]
class MenusCreate extends Component
{
    use AuthorizesRequests;

    public string $name = '';
    public ?int $parent_id = null;
    public ?string $route_name = null;
    public ?string $icon = null;
    public int $order = 0;

    public function mount(): void
    {
        $this->authorize('menu.create');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255', Rule::unique(Menu::class, 'route_name')],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        Menu::create($data);

        session()->flash('success', 'Menu baru berhasil ditambahkan.');

        $this->redirectRoute('menus.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.menus.create', [
            'parentMenuOptions' => $this->parentMenuOptions(),
            'icons' => IconHelper::getIcons(),
        ])->layoutData([
            'title' => 'Tambah Menu',
        ]);
    }

    protected function parentMenuOptions(): array
    {
        return Menu::orderBy('name')
            ->get()
            ->map(fn($menu) => [
                'id' => $menu->id,
                'label' => $menu->name,
            ])
            ->toArray();
    }

}
