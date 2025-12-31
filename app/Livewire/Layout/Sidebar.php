<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Collection;
use Livewire\Component;

class Sidebar extends Component
{
    public Collection $sidebarMenus;

    public function mount($sidebarMenus = null): void
    {
        $this->sidebarMenus = $this->prepareMenus($sidebarMenus);
    }

    protected function prepareMenus($sidebarMenus): Collection
    {
        if ($sidebarMenus instanceof Collection) {
            return $sidebarMenus;
        }

        if (! empty($sidebarMenus)) {
            return collect($sidebarMenus);
        }

        return $this->resolveSidebarMenus();
    }

    protected function resolveSidebarMenus(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        $user->loadMissing('roles.menus.children');

        $menus = $user->roles
            ->flatMap(fn ($role) => $role->menus)
            ->unique('id');

        return $menus
            ->whereNull('parent_id')
            ->sortBy('order')
            ->values()
            ->map(function ($menu) {
                $children = $menu->children instanceof Collection
                    ? $menu->children->sortBy('order')->values()
                    : collect();

                $menu->setRelation('children', $children);

                return $menu;
            });
    }

    public function render()
    {
        return view('livewire.layout.sidebar', [
            'sidebarMenus' => $this->sidebarMenus,
        ]);
    }
}
