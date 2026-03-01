<?php

namespace App\Models;

use App\Traits\BranchScoped;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped;

    protected $table = 'acc_journals';

    protected $fillable = [
        'branch_id',
        'journal_no',
        'journal_date',
        'description',
        'source_type',
        'source_id',
        'total_debit',
        'total_credit',
        'posted_by',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines()
    {
        return $this->hasMany(AccountingJournalLine::class, 'journal_id');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}

