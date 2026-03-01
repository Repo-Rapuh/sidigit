<?php

namespace App\Repositories;

use App\Models\AccountingJournal;
use Illuminate\Database\Eloquent\Builder;

class AccountingJournalRepository
{
    public function query(): Builder
    {
        return AccountingJournal::query();
    }

    public function findOrFail(int $id): AccountingJournal
    {
        return AccountingJournal::query()->findOrFail($id);
    }

    public function create(array $data): AccountingJournal
    {
        return AccountingJournal::query()->create($data);
    }
}

