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
#[Title('Edit Role')]
class RolesEdit extends Component
{
    use AuthorizesRequests;

    public Role $role;
    public string $name = '';
    public string $slug = '';
    public array $permissions = [];
    public array $menus = [];

    public function mount(Role $role): void
    {
        $this->authorize('role.edit');

        $this->role = $role->load(['permissions', 'menus']);

        $this->name = $this->role->name;
        $this->slug = $this->role->slug;
        $this->permissions = $this->role->permissions->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->menus = $this->role->menus->pluck('id')->map(fn ($id) => (int) $id)->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class, 'name')->ignore($this->role->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Role::class, 'slug')->ignore($this->role->id)],
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

    public function update(): void
    {
        $data = $this->validate();

        $this->role->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);

        $this->role->permissions()->sync($this->permissions);
        $this->role->menus()->sync($this->menus);

        session()->flash('success', 'Role berhasil diperbarui.');

        $this->redirectRoute('roles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.roles.edit', [
            'menus' => $this->loadMenus(),
        ])->layoutData([
            'title' => 'Edit Role',
        ]);
    }
}
