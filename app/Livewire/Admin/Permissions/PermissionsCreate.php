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
#[Title('Tambah Permission')]
class PermissionsCreate extends Component
{
    use AuthorizesRequests;

    public string $name = '';
    public string $slug = '';
    public ?int $menu_id = null;

    public function mount(): void
    {
        $this->authorize('permission.create');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Permission::class, 'slug')],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        Permission::create($data);

        session()->flash('success', 'Permission berhasil dibuat.');

        $this->redirectRoute('permissions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.permissions.create', [
            'menus' => Menu::orderBy('name')->get(),
        ])->layoutData([
            'title' => 'Tambah Permission',
        ]);
    }
}
