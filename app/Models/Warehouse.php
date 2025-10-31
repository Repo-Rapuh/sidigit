<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Warehouse extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    protected $table = 'mst_warehouses';

    protected $fillable = [
        'name',
        'location_lat',
        'location_lng',
        'description',
    ];

    protected $casts = [
        'location_lat' => 'float',
        'location_lng' => 'float',
    ];
}
