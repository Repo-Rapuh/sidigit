<?php

namespace App\Repositories;

use App\Models\BankAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BankAccountRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return BankAccount::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): BankAccount
    {
        return BankAccount::query()->findOrFail($id);
    }

    public function create(array $data): BankAccount
    {
        return BankAccount::query()->create($data);
    }

    public function update(BankAccount $bankAccount, array $data): BankAccount
    {
        $bankAccount->update($data);

        return $bankAccount;
    }

    public function delete(BankAccount $bankAccount): void
    {
        $bankAccount->delete();
    }
}
