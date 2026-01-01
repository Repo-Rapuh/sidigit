<?php

namespace App\Repositories;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Warehouse::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Warehouse
    {
        return Warehouse::query()->findOrFail($id);
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::query()->create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse;
    }

    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }
}
