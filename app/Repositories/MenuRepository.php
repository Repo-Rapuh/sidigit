<?php

namespace App\Repositories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

final class MenuRepository
{
    public function all(): Collection
    {
        return Menu::query()->with('children')
            ->select(['id', 'name', 'route_name', 'parent_id', 'icon'])
            ->orderBy('order')
            ->get();
    }

    public function tree(): Collection
    {
        return Menu::query()->whereNull('parent_id')
            ->with('children.children')->orderBy('order')->get();
    }
}
