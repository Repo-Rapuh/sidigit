<?php

namespace App\Dto;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class MenuItemData extends Data
{
    public string $name;
    public string $route_name;
    public ?string $route_url = null;
    public string $icon;
    public bool $active;
    public ?int $parent_id;

    #[DataCollectionOf(MenuItemData::class)]
    public Collection $children;

    public function __construct(
        string $name,
        string $route_name,
        ?string $route_url = null,
        string $icon,
        bool $active,
        ?int $parent_id,
        Collection $children,
    ) {
        $this->name = $name;
        $this->route_name = $route_name;
        $this->route_url = $route_url;
        $this->icon = $icon;
        $this->active = $active;
        $this->parent_id = $parent_id;
        $this->children = $children;
    }

    public static function fromModel($menu): self
    {
        $base = filled($menu->route_name) ? Str::before($menu->route_name, '.') : null;
        $children = $menu->children instanceof Collection ? $menu->children->map(fn($child) => self::fromModel($child)) : collect();

        $patterns = collect([$menu->route_name])
            ->merge($menu->children?->pluck('route_name') ?? collect())
            ->filter()
            ->flatMap(fn($name) => self::buildRoutePatterns($name))
            ->unique()
            ->values()
            ->all();

        return new self(
            name: $menu->name,
            route_name: $menu->route_name ?? '',
            route_url: filled($menu->route_name) && Route::has($menu->route_name) ? route($menu->route_name) : '#',
            icon: self::resolveIcon($menu, $base),
            active: !empty($patterns) ? Route::is($patterns) : false,
            parent_id: null,
            children: $children,
        );
    }

    protected static function resolveIcon($menu, ?string $base): string
    {
        $icons = config('menu.icons');
        if (! is_array($icons)) {
            $icons = [];
        }
        $default = $icons['default'] ?? '';

        if (filled($menu->icon)) {
            if (str_contains($menu->icon, '<svg')) {
                return $menu->icon;
            }

            if (isset($icons[$menu->icon]) && filled($icons[$menu->icon])) {
                return $icons[$menu->icon];
            }

            return $menu->icon;
        }

        if (filled($base) && isset($icons[$base]) && filled($icons[$base])) {
            return $icons[$base];
        }

        return $default;
    }

    protected static function buildRoutePatterns(?string $routeName): array
    {
        if (!filled($routeName)) {
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
}
