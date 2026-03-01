<?php

namespace App\Models;

use App\Traits\BranchScoped;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionJobLog extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped;

    protected $table = 'production_job_logs';

    protected $fillable = [
        'branch_id',
        'production_job_id',
        'order_id',
        'event',
        'from_status',
        'to_status',
        'note',
        'changed_by',
    ];

    public function job()
    {
        return $this->belongsTo(ProductionJob::class, 'production_job_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
