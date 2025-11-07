<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Menu')]
class MenusIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('menu.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteMenu(int $menuId): void
    {
        $this->authorize('menu.delete');

        $menu = Menu::findOrFail($menuId);
        $menu->delete();

        session()->flash('success', 'Menu berhasil dihapus.');
    }

    public function getMenusProperty()
    {
        return Menu::with('parent')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('route_name', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.menus.index', [
            'menus' => $this->menus,
        ])->layoutData([
            'title' => 'Manajemen Menu',
        ]);
    }
}
