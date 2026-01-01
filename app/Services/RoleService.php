<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class RoleService
{
    public function query(): Builder
    {
        return Role::query();
    }

    public function destroy(int $id): void
    {
        Role::query()->whereKey($id)->delete();
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Role::query()->whereIn('id', $ids)->delete();
    }
}
