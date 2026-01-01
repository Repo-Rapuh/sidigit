<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Builder;

class MenuService
{
    public function query(): Builder
    {
        return Menu::query()->with('parent');
    }

    public function find(int $id): Menu
    {
        return Menu::findOrFail($id);
    }

    public function store(array $data): Menu
    {
        return Menu::create($data);
    }

    public function update(int $id, array $data): Menu
    {
        $menu = $this->find($id);
        $menu->update($data);

        return $menu;
    }

    public function destroy(int $id): void
    {
        $menu = $this->find($id);
        $menu->delete();
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Menu::query()->whereIn('id', $ids)->delete();
    }
}
