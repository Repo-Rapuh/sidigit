<?php

namespace App\Dto;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class MenuItemData extends Data
{
    public function __construct(
        public string $name,
        public string $route_name,
        public ?string $route_url = null,
        public string $icon,
        public bool $active,
        public ?int $parent_id,

        #[DataCollectionOf(MenuItemData::class)]
        public Collection $children,
    ) {}

    public static function fromModel($menu): self
    {
        $base = filled($menu->route_name) ? Str::before($menu->route_name, '.') : null;
        $children = $menu->children instanceof Collection ? $menu->children->map(fn($child) => self::fromModel($child)) : collect();

        $patterns = collect([$menu->route_name])
            ->merge($menu->children?->pluck('route_name') ?? collect())
            ->filter()
            ->flatMap(fn($name) => [
                $name,
                Str::before($name, '.') . '.*',
            ])
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
            return str_contains($menu->icon, '<svg') ? $menu->icon : ($icons[$menu->icon] ?? $default);
        }

        return $icons[$base] ?? $default;
    }
}
