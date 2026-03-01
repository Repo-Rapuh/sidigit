<?php

namespace App\Models;

use App\Traits\BranchScoped;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped;

    protected $table = 'acc_accounts';

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function journalLines()
    {
        return $this->hasMany(AccountingJournalLine::class, 'account_id');
    }
}

