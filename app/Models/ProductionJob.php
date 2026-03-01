<?php

namespace App\Models;

use App\Traits\BranchScoped;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class ProductionJob extends Model
{
    use HasFactory, LogsAllActivity, BranchScoped, BlameableTrait, SoftDeletes;

    public const STAGE_DESAIN = 'desain';
    public const STAGE_PRODUKSI = 'produksi';

    public const STATUS_ANTRIAN = 'antrian';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_QC = 'qc';
    public const STATUS_SIAP_DIAMBIL = 'siap_diambil';

    protected $table = 'production_jobs';

    protected $fillable = [
        'branch_id',
        'order_id',
        'order_item_id',
        'stage',
        'assigned_role_id',
        'claimed_by',
        'claimed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    public static function stageOptions(): array
    {
        return [
            self::STAGE_DESAIN => 'Desain',
            self::STAGE_PRODUKSI => 'Produksi',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ANTRIAN => 'Antrian',
            self::STATUS_IN_PROGRESS => 'Produksi',
            self::STATUS_QC => 'QC',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function claimedByUser()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function logs()
    {
        return $this->hasMany(ProductionJobLog::class, 'production_job_id');
    }
}
