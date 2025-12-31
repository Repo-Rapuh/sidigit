<?php

namespace App\Livewire\Admin\Permissions;

use App\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Permission')]
class PermissionsIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('permission.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deletePermission(int $permissionId): void
    {
        $this->authorize('permission.delete');

        $permission = Permission::findOrFail($permissionId);
        $permission->delete();

        session()->flash('success', 'Permission berhasil dihapus.');
    }

    public function getPermissionsProperty()
    {
        return Permission::with('menu')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.permissions.index', [
            'permissions' => $this->permissions,
        ])->layoutData([
            'title' => 'Manajemen Permission',
        ]);
    }
}
