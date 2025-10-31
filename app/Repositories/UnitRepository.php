<?php

namespace App\Repositories;

use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UnitRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Unit::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Unit
    {
        return Unit::query()->findOrFail($id);
    }

    public function create(array $data): Unit
    {
        return Unit::query()->create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit;
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }
}
