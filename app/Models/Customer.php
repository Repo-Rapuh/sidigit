<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Customer extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    public const MEMBER_TYPES = ['umum', 'reseller', 'sekolah', 'pemerintah', 'swasta'];

    protected $table = 'mst_customers';

    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'email',
        'member_type',
    ];
}
