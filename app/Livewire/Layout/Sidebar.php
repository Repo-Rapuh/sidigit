<?php

namespace App\Livewire\Layout;

use App\Dto\MenuItemData;
use App\Models\Branch;
use App\Services\MenuCacheService;
use App\Support\UploadStorage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
        $activeBranch = $this->resolveActiveBranch();
        return view('livewire.layout.sidebar', [
            'sidebarMenus' => $this->withActiveState($this->sidebarMenus),
            'branchLogoUrl' => $this->resolveBranchLogoUrl($activeBranch),
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

        $patterns = collect($routeName ? $this->buildRoutePatterns($routeName) : [])
            ->merge($childRoutes->flatMap(fn($child) => $this->buildRoutePatterns($child)))
            ->filter()->unique()->values()->all();

        return ! empty($patterns) ? Route::is($patterns) : false;
    }

    protected function isRouteActive(?string $routeName): bool
    {
        if ($this->looksLikeUrl($routeName)) {
            return $this->urlsMatch($this->currentUrl(), $routeName);
        }

        $patterns = $this->buildRoutePatterns($routeName);

        return ! empty($patterns) ? Route::is($patterns) : false;
    }

    protected function buildRoutePatterns(?string $routeName): array
    {
        if (!filled($routeName) || $this->looksLikeUrl($routeName)) {
            return [];
        }

        $suffixes = ['index', 'create', 'edit', 'show', 'trashed'];
        $last = Str::afterLast($routeName, '.');
        $base = in_array($last, $suffixes, true) ? Str::beforeLast($routeName, '.') : $routeName;

        return collect([$routeName, filled($base) ? $base . '.*' : null])
            ->filter()
            ->unique()
            ->values()
            ->all();
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

    protected function resolveActiveBranch(): ?Branch
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $activeId = session('active_branch_id') ?? $user->branch_id;
        if (! $activeId) {
            return null;
        }

        return Branch::query()->whereKey($activeId)->first();
    }

    protected function resolveBranchLogoUrl(?Branch $branch): ?string
    {
        if (! $branch || empty($branch->logo_path)) {
            return null;
        }

        $disk = UploadStorage::disk();

        try {
            $storage = Storage::disk($disk);
            $driver = config("filesystems.disks.{$disk}.driver");

            return $driver === 's3'
                ? $storage->temporaryUrl($branch->logo_path, now()->addMinutes(10))
                : $storage->url($branch->logo_path);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
