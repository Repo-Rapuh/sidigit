<?php

namespace App\Livewire\Admin\Permissions;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Permission')]
class PermissionsEdit extends Component
{
    use AuthorizesRequests;

    public Permission $permission;
    public string $name = '';
    public string $slug = '';
    public ?int $menu_id = null;

    public function mount(Permission $permission): void
    {
        $this->authorize('permission.edit');

        $this->permission = $permission;

        $this->name = $permission->name;
        $this->slug = $permission->slug;
        $this->menu_id = $permission->menu_id;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Permission::class, 'slug')->ignore($this->permission->id)],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        $this->permission->update($data);

        session()->flash('success', 'Permission berhasil diperbarui.');

        $this->redirectRoute('permissions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.permissions.edit', [
            'menus' => Menu::orderBy('name')->get(),
        ])->layoutData([
            'title' => 'Edit Permission',
        ]);
    }
}
