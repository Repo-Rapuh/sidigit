<?php

namespace App\Models;

// 1. Tambahkan use statement ini
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Employee;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class User extends Authenticatable
{
    // 2. Tambahkan trait ini di dalam class
    use HasFactory, Notifiable, LogsAllActivity, SoftDeletes, BlameableTrait;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'branch_id',
        'employee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // <-- INI YANG PALING PENTING!
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user')->withTimestamps();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function hasRoleSlug(array|string $slugs): bool
    {
        $slugs = (array) $slugs;

        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function isBranchSuperAdmin(): bool
    {
        return $this->hasRoleSlug(['superadmin', 'admin', 'owner']);
    }
}
