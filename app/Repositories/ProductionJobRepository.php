<?php

namespace App\Repositories;

use App\Models\ProductionJob;
use Illuminate\Database\Eloquent\Builder;

class ProductionJobRepository
{
    public function query(): Builder
    {
        return ProductionJob::query();
    }

    public function findOrFail(int $id): ProductionJob
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): ProductionJob
    {
        return $this->query()->create($data);
    }

    public function update(ProductionJob $job, array $data): ProductionJob
    {
        $job->update($data);

        return $job;
    }
}
