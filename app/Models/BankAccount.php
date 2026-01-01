<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class BankAccount extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;
    protected $table = 'mst_bank_accounts';
    protected $fillable = [
        'rekening_number',
        'account_name',
        'bank_name',
    ];
}
