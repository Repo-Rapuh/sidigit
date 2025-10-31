<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function find(int $id): Category
    {
        return $this->repository->findOrFail($id);
    }
}
