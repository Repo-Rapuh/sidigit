<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Role')]
class RolesCreate extends Component
{
    use AuthorizesRequests;

    public string $name = '';
    public string $slug = '';
    public array $permissions = [];
    public array $menus = [];

    public function mount(): void
    {
        $this->authorize('role.create');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class, 'name')],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Role::class, 'slug')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'menus' => ['nullable', 'array'],
            'menus.*' => ['exists:menus,id'],
        ];
    }

    protected function loadMenus()
    {
        return Menu::whereNull('parent_id')
            ->with(['children.permissions', 'permissions'])
            ->orderBy('order')
            ->get();
    }

    public function save(): void
    {
        $data = $this->validate();

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);

        $role->permissions()->sync($this->permissions);
        $role->menus()->sync($this->menus);

        session()->flash('success', 'Role baru berhasil ditambahkan.');

        $this->redirectRoute('roles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.roles.create', [
            'menus' => $this->loadMenus(),
        ])->layoutData([
            'title' => 'Tambah Role',
        ]);
    }
}
