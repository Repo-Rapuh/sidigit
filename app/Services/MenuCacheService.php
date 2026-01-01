<?php

namespace App\Services;

use App\Dto\MenuItemData;
use App\Models\User;
use App\Repositories\MenuRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuCacheService
{
    public function __construct(
        protected MenuRepository $repository
    ) {}

    public function filter(Collection $menus, User $user): Collection
    {
        return $menus
            ->filter(fn ($menu) =>
                !$menu->permission_name || $user->can($menu->permission_name)
            )
            ->map(function ($menu) use ($user) {
                $menu = clone $menu;
                if ($menu->children?->isNotEmpty()) {
                    $menu->children = $this->filter($menu->children, $user);
                }

                return $menu;
            })
            ->filter(fn ($menu) =>
                filled($menu->route_name) || $menu->children->isNotEmpty()
            )
            ->values();
    }

    public function sidebarForUser(User $user): Collection
    {
        $key = $this->cacheKey($user);

        return Cache::rememberForever($key, function () use ($user) {
            $menus = $this->repository->tree();
            $visible = $this->filter($menus, $user);

            return $visible->map(
                fn ($menu) => MenuItemData::fromModel($menu)
            );
        });
    }

    protected function cacheKey(User $user): string
    {
        $permissions = $user->roles()
            ->with('permissions:id,slug')
            ->get()
            ->flatMap(fn ($role) => $role->permissions)
            ->pluck('slug')
            ->filter()
            ->unique()
            ->sort()
            ->implode('|');

        return 'sidebar.menus.user.' . $user->id . '.' . sha1($permissions);
    }
}
