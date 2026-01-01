<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CategoryService
{
    protected $repository;
    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function query(): Builder
    {
        return Category::query();
    }

    public function store(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = $this->repository->findOrFail($id);

        return $this->repository->update($category, $data);
    }

    public function destroy(int $id): void
    {
        $category = $this->repository->findOrFail($id);

        $this->repository->delete($category);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        Category::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): Category
    {
        return $this->repository->findOrFail($id);
    }
}
