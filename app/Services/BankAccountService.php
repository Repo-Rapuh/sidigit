<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Repositories\BankAccountRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;


class BankAccountService
{
    protected $repository;
    public function __construct(BankAccountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function query(): Builder
    {
        return BankAccount::query();
    }

    public function store(array $data): BankAccount
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): BankAccount
    {
        $bankAccount = $this->repository->findOrFail($id);

        return $this->repository->update($bankAccount, $data);
    }

    public function destroy(int $id): void
    {
        $bankAccount = $this->repository->findOrFail($id);

        $this->repository->delete($bankAccount);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        BankAccount::query()->whereIn('id', $ids)->delete();
    }

    public function find(int $id): BankAccount
    {
        return $this->repository->findOrFail($id);
    }
}
