<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Repositories\WarehouseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class WarehouseService
{
    protected $repository;
    public function __construct(WarehouseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function query(): Builder
    {
        return Warehouse::query();
    }

    public function store(array $data): Warehouse
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Warehouse
    {
        $warehouse = $this->repository->findOrFail($id);

        return $this->repository->update($warehouse, $data);
    }

    public function destroy(int $id): void
    {
        $warehouse = $this->repository->findOrFail($id);

        $this->repository->delete($warehouse);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Warehouse::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): Warehouse
    {
        return $this->repository->findOrFail($id);
    }
}
