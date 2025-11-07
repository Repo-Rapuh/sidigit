<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Role')]
class RolesIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('role.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorize('role.delete');

        $role = Role::findOrFail($roleId);
        $role->delete();

        session()->flash('success', 'Role berhasil dihapus.');
    }

    public function getRolesProperty()
    {
        return Role::when($this->search !== '', function ($query) {
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
        return view('livewire.admin.roles.index', [
            'roles' => $this->roles,
        ])->layoutData([
            'title' => 'Manajemen Role',
        ]);
    }
}
