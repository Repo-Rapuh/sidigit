<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Menu;
use App\Models\Role;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Role')]
class RolesCreate extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $name = '';
    public array $permissions = [];
    public array $menus = [];
    public bool $menuOptionsReady = false;
    public array $menuPermissions = [];
    public array $childPermissions = [];
    public array $menuPermissionsLoaded = [];
    public bool $selectAllMenus = false;
    public bool $selectAllPermissions = false;

    public function mount(): void
    {
        $this->authorize('role.create');
        $this->setPageMeta(
            'Tambah Role Baru',
            'Tentukan nama peran, dan hak aksesnya.',
            [
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => true],
                ['label' => 'Role', 'url' => route('roles.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class, 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'menus' => ['nullable', 'array'],
            'menus.*' => ['exists:menus,id'],
        ];
    }

    protected function loadMenus()
    {
        return Cache::remember('roles.menu-options', 600, function () {
            return Menu::whereNull('parent_id')
                ->with(['children'])
                ->orderBy('order')
                ->get();
        });
    }

    public function loadMenuOptions(): void
    {
        $this->menuOptionsReady = true;
    }

    protected function loadMenusWithPermissions()
    {
        return Cache::remember('roles.menu-options-with-permissions', 600, function () {
            return Menu::whereNull('parent_id')
                ->with(['children.permissions', 'permissions'])
                ->orderBy('order')
                ->get();
        });
    }

    public function loadMenuPermissions(int $menuId): void
    {
        if ($this->menuPermissionsLoaded[$menuId] ?? false) {
            return;
        }

        $menu = Menu::with(['permissions', 'children.permissions'])->find($menuId);
        if (! $menu) {
            return;
        }

        $this->menuPermissions[$menuId] = $menu->permissions
            ->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])
            ->values()
            ->all();

        foreach ($menu->children as $child) {
            $this->childPermissions[$child->id] = $child->permissions
                ->map(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ])
                ->values()
                ->all();
        }

        $this->menuPermissionsLoaded[$menuId] = true;
    }

    public function updatedSelectAllMenus(bool $value): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        if ($value) {
            $ids = $this->loadMenus()
                ->flatMap(fn($menu) => collect([$menu->id])->merge($menu->children->pluck('id')))
                ->values()
                ->all();
            $this->menus = $ids;
        } else {
            $this->menus = [];
        }
    }

    public function updatedSelectAllPermissions(bool $value): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        if ($value) {
            $menus = $this->loadMenusWithPermissions();

            $ids = $menus->flatMap(function ($menu) {
                return $menu->permissions->pluck('id')
                    ->merge($menu->children->flatMap(fn($child) => $child->permissions->pluck('id')));
            })
                ->unique()
                ->values()
                ->all();

            $this->menuPermissionsLoaded = [];
            $this->menuPermissions = [];
            $this->childPermissions = [];
            foreach ($menus as $menu) {
                $this->menuPermissions[$menu->id] = $menu->permissions
                    ->map(fn($permission) => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ])
                    ->values()
                    ->all();

                foreach ($menu->children as $child) {
                    $this->childPermissions[$child->id] = $child->permissions
                        ->map(fn($permission) => [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ])
                        ->values()
                        ->all();
                }

                $this->menuPermissionsLoaded[$menu->id] = true;
            }

            $this->permissions = $ids;
        } else {
            $this->permissions = [];
        }
    }

    public function updatedMenus(): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        $total = $this->loadMenus()
            ->flatMap(fn($menu) => collect([$menu->id])->merge($menu->children->pluck('id')))
            ->unique()
            ->count();

        $this->selectAllMenus = $total > 0 && count(array_unique($this->menus)) === $total;
    }

    public function updatedPermissions(): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        $total = $this->loadMenusWithPermissions()
            ->flatMap(function ($menu) {
                return $menu->permissions->pluck('id')
                    ->merge($menu->children->flatMap(fn($child) => $child->permissions->pluck('id')));
            })
            ->unique()
            ->count();

        $this->selectAllPermissions = $total > 0 && count(array_unique($this->permissions)) === $total;
    }

    public function save(): void
    {
        $data = $this->validate();

        $role = Role::create([
            'name' => $data['name'],
        ]);

        $role->permissions()->sync($this->permissions);
        $role->menus()->sync($this->menus);

        session()->flash('toast', [
            'message' => 'Role baru berhasil ditambahkan.',
            'type' => 'success',
        ]);

        $this->redirectRoute('roles.index');
    }

    public function render()
    {
        return view('livewire.admin.roles.create', [
            'menuOptions' => $this->menuOptionsReady ? $this->loadMenus() : collect(),
        ]);
    }
}
