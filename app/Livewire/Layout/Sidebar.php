<?php

namespace App\Livewire\Layout;

use App\Dto\MenuItemData;
use App\Services\MenuCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;

class Sidebar extends Component
{
    protected Collection $sidebarMenus;

    public function mount($sidebarMenus = null): void
    {
        $this->sidebarMenus = $this->prepareMenus($sidebarMenus);
    }

    protected function prepareMenus($sidebarMenus): Collection
    {
        if ($sidebarMenus instanceof Collection) {
            $first = $sidebarMenus->first();
            if ($first instanceof MenuItemData || $first === null) {
                return $sidebarMenus;
            }

            return $sidebarMenus->map(fn($menu) => MenuItemData::fromModel($menu));
        }

        if (! empty($sidebarMenus)) {
            $menus = collect($sidebarMenus);
            $first = $menus->first();

            if ($first instanceof MenuItemData || $first === null) {
                return $menus;
            }

            return $menus->map(fn($menu) => MenuItemData::fromModel($menu));
        }

        return $this->resolveSidebarMenus();
    }

    protected function resolveSidebarMenus(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return app(MenuCacheService::class)->sidebarForUser($user);
    }

    public function render()
    {
        return view('livewire.layout.sidebar', [
            'sidebarMenus' => $this->withActiveState($this->sidebarMenus),
        ]);
    }

    protected function withActiveState(Collection $menus): Collection
    {
        return $menus->map(function ($menu) {
            $menu = clone $menu;
            $children = $menu->children instanceof Collection ? $menu->children : collect();

            $children = $children->map(function ($child) {
                $child = clone $child;
                $child->active = $this->isRouteActive($child->route_name ?? null);

                return $child;
            });

            $menu->children = $children;
            $menu->active = $this->isMenuActive($menu->route_name ?? null, $children);

            return $menu;
        });
    }

    protected function isMenuActive(?string $routeName, Collection $children): bool
    {
        $currentUrl = $this->currentUrl();
        $urlMatches = collect([$routeName])
            ->merge($children->pluck('route_name'))
            ->filter(fn($value) => $this->looksLikeUrl($value))
            ->contains(fn($value) => $this->urlsMatch($currentUrl, $value));

        if ($urlMatches) return true;

        $childRoutes = $children->pluck('route_name')->filter(fn($value) => ! $this->looksLikeUrl($value));

        $routeName = $this->looksLikeUrl($routeName) ? null : $routeName;

        $patterns = collect([$routeName, filled($routeName) ? Str::before($routeName, '.') . '.*' : null])
            ->merge($childRoutes)->merge($childRoutes->map(fn($child) => Str::before($child, '.') . '.*'))
            ->filter()->unique()->values()->all();

        return ! empty($patterns) ? Route::is($patterns) : false;
    }

    protected function isRouteActive(?string $routeName): bool
    {
        if ($this->looksLikeUrl($routeName)) {
            return $this->urlsMatch($this->currentUrl(), $routeName);
        }

        $patterns = collect([$routeName, filled($routeName) ? Str::before($routeName, '.') . '.*' : null])
            ->filter()->values()->all();

        return ! empty($patterns) ? Route::is($patterns) : false;
    }

    protected function looksLikeUrl(?string $value): bool
    {
        return filled($value) && Str::startsWith($value, ['http://', 'https://', '/']);
    }

    protected function currentUrl(): string
    {
        return rtrim(url()->current(), '/');
    }

    protected function urlsMatch(string $current, string $target): bool
    {
        return rtrim($current, '/') === rtrim($target, '/');
    }
}
