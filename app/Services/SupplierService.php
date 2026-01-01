<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SupplierService
{
    protected $repository;
    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function query(): Builder
    {
        return Supplier::query();
    }

    public function store(array $data): Supplier
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Supplier
    {
        $supplier = $this->repository->findOrFail($id);

        return $this->repository->update($supplier, $data);
    }

    public function destroy(int $id): void
    {
        $supplier = $this->repository->findOrFail($id);

        $this->repository->delete($supplier);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Supplier::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): Supplier
    {
        return $this->repository->findOrFail($id);
    }
}
