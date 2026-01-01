<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class PermissionService
{
    public function query(): Builder
    {
        return Permission::query()->with('menu');
    }

    public function find(int $id): Permission
    {
        return Permission::findOrFail($id);
    }

    public function store(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(int $id, array $data): Permission
    {
        $permission = $this->find($id);
        $permission->update($data);

        return $permission;
    }

    public function destroy(int $id): void
    {
        $permission = $this->find($id);
        $permission->delete();
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Permission::query()->whereIn('id', $ids)->delete();
    }
}
