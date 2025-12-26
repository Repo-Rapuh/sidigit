<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function query(): Builder
    {
        return Product::query()->with(['category', 'unit']);
    }

    public function store(array $data): Product
    {
        $materials = $data['materials'] ?? [];
        unset($data['materials']);
        $this->normaliseDimensions($data);

        return DB::transaction(function () use ($data, $materials) {
            /** @var \App\Models\Product $product */
            $product = $this->repository->create($data);
            $this->repository->attachMaterials($product, $this->prepareMaterials($materials));

            return $this->repository->findOrFail($product->id);
        });
    }

    public function update(int $id, array $data): Product
    {
        $materials = $data['materials'] ?? [];
        unset($data['materials']);
        $this->normaliseDimensions($data);

        return DB::transaction(function () use ($id, $data, $materials) {
            $product = $this->repository->findOrFail($id);
            $this->repository->update($product, $data);
            $this->repository->attachMaterials($product, $this->prepareMaterials($materials));

            return $this->repository->findOrFail($product->id);
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $product = $this->repository->findOrFail($id);
            $product->productMaterials()->delete();
            $this->repository->delete($product);
        });
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            $products = Product::query()->whereIn('id', $ids)->get();
            foreach ($products as $product) {
                $product->productMaterials()->delete();
            }

            Product::query()->whereIn('id', $ids)->delete();
        });
    }

    public function find(int $id): Product
    {
        return $this->repository->findOrFail($id);
    }

    protected function prepareMaterials(array $materials): array
    {
        return collect($materials)
            ->map(function ($material) {
                if (is_array($material)) {
                    return [
                        'material_id' => $material['material_id'] ?? $material['id'] ?? null,
                        'quantity' => $material['quantity'] ?? 1,
                    ];
                }

                return [
                    'material_id' => $material,
                    'quantity' => 1,
                ];
            })
            ->filter(fn ($item) => !empty($item['material_id']))
            ->unique('material_id')
            ->values()
            ->toArray();
    }

    protected function normaliseDimensions(array &$data): void
    {
        $data['length_cm'] = isset($data['length_cm']) && $data['length_cm'] !== null
            ? $data['length_cm']
            : 0;

        $data['width_cm'] = isset($data['width_cm']) && $data['width_cm'] !== null
            ? $data['width_cm']
            : 0;
    }
}
