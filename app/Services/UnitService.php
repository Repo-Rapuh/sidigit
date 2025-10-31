<?php

namespace App\Services;

use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UnitService
{
    protected $repository;
    public function __construct(UnitRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function store(array $data): Unit
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Unit
    {
        $unit = $this->repository->findOrFail($id);

        return $this->repository->update($unit, $data);
    }

    public function destroy(int $id): void
    {
        $unit = $this->repository->findOrFail($id);

        $this->repository->delete($unit);
    }

    public function find(int $id): Unit
    {
        return $this->repository->findOrFail($id);
    }
}
