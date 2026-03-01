<?php

namespace App\Repositories;

use App\Models\AccountingAccount;
use Illuminate\Database\Eloquent\Builder;

class AccountingAccountRepository
{
    public function query(): Builder
    {
        return AccountingAccount::query();
    }

    public function findOrFail(int $id): AccountingAccount
    {
        return AccountingAccount::query()->findOrFail($id);
    }

    public function create(array $data): AccountingAccount
    {
        return AccountingAccount::query()->create($data);
    }

    public function update(AccountingAccount $account, array $data): AccountingAccount
    {
        $account->update($data);

        return $account;
    }

    public function delete(AccountingAccount $account): void
    {
        $account->delete();
    }
}

