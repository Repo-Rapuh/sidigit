<?php

namespace App\Services;

use App\Dto\MenuItemData;
use App\Models\User;
use App\Repositories\MenuRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuCacheService
{
    protected string $cacheVersion = 'v3';
    protected $repository;
    public function __construct(MenuRepository $repository)
    {
        $this-> repository = $repository;
    }

    public function filter(Collection $menus, User $user, Collection $allowedMenuIds): Collection
    {
        return $menus
            ->map(function ($menu) use ($user, $allowedMenuIds) {
                $menu = clone $menu;
                if ($menu->children?->isNotEmpty()) {
                    $menu->children = $this->filter($menu->children, $user, $allowedMenuIds);
                }

                return $menu;
            })
            ->filter(function ($menu) use ($user, $allowedMenuIds) {
                $children = $menu->children instanceof Collection ? $menu->children : collect();
                $hasChildren = $children->isNotEmpty();
                $allowed = $allowedMenuIds->contains($menu->id);
                $permissionName = $menu->permission_name ?? null;
                $passesPermission = !$permissionName || $user->can($permissionName);

                $visible = ($allowed && $passesPermission) || $hasChildren;

                if (!$visible) {
                    return false;
                }

                return filled($menu->route_name) || $hasChildren;
            })
            ->values();
    }

    public function sidebarForUser(User $user): Collection
    {
        $key = $this->cacheKey($user);

        return Cache::rememberForever($key, function () use ($user) {
            $menus = $this->repository->tree();
            $allowedMenuIds = $this->allowedMenuIds($user);
            $visible = $this->filter($menus, $user, $allowedMenuIds);

            return $visible->map(
                fn ($menu) => MenuItemData::fromModel($menu)
            );
        });
    }

    protected function allowedMenuIds(User $user): Collection
    {
        return $user->menuIds();
    }

    protected function cacheKey(User $user): string
    {
        $menuIds = $user->menuIds()
            ->sort()
            ->implode('|');

        $permissions = $user->permissionSlugs()
            ->sort()
            ->implode('|');

        return 'sidebar.menus.user.' . $user->id . '.' . $this->cacheVersion . '.' . sha1($menuIds . '|' . $permissions);
    }
}
