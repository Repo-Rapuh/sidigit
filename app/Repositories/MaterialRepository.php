<?php

namespace App\Repositories;

use App\Models\Material;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MaterialRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Material::query()
            ->with(['category', 'unit'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Material
    {
        return Material::query()->with(['category', 'unit'])->findOrFail($id);
    }

    public function create(array $data): Material
    {
        return Material::query()->create($data);
    }

    public function update(Material $material, array $data): Material
    {
        $material->update($data);

        return $material;
    }

    public function delete(Material $material): void
    {
        $material->delete();
    }
}
