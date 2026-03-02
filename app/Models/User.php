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
use Illuminate\Support\Collection;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class User extends Authenticatable
{
    // 2. Tambahkan trait ini di dalam class
    use HasFactory, Notifiable, LogsAllActivity, SoftDeletes, BlameableTrait;

    protected ?Collection $cachedRoleSlugs = null;

    protected ?Collection $cachedPermissionSlugs = null;

    protected ?Collection $cachedMenuIds = null;


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
        $lookup = collect((array) $slugs)
            ->filter(fn ($slug) => filled($slug))
            ->map(fn ($slug) => (string) $slug);

        if ($lookup->isEmpty()) {
            return false;
        }

        return $this->roleSlugs()->intersect($lookup)->isNotEmpty();
    }

    public function hasPermissionSlug(string $slug): bool
    {
        if (! filled($slug)) {
            return false;
        }

        return $this->permissionSlugs()->contains($slug);
    }

    public function isBranchSuperAdmin(): bool
    {
        return $this->hasRoleSlug(['superadmin', 'admin', 'owner']);
    }

    public function roleSlugs(): Collection
    {
        if ($this->cachedRoleSlugs instanceof Collection) {
            return $this->cachedRoleSlugs;
        }

        $this->cachedRoleSlugs = $this->roles()
            ->pluck('roles.slug')
            ->filter()
            ->map(fn ($slug) => (string) $slug)
            ->values();

        return $this->cachedRoleSlugs;
    }

    public function permissionSlugs(): Collection
    {
        if ($this->cachedPermissionSlugs instanceof Collection) {
            return $this->cachedPermissionSlugs;
        }

        $this->cachedPermissionSlugs = $this->roles()
            ->with('permissions:id,slug')
            ->get()
            ->flatMap(fn ($role) => $role->permissions)
            ->pluck('slug')
            ->filter()
            ->map(fn ($slug) => (string) $slug)
            ->unique()
            ->values();

        return $this->cachedPermissionSlugs;
    }

    public function menuIds(): Collection
    {
        if ($this->cachedMenuIds instanceof Collection) {
            return $this->cachedMenuIds;
        }

        $this->cachedMenuIds = $this->roles()
            ->with('menus:id')
            ->get()
            ->flatMap(fn ($role) => $role->menus)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return $this->cachedMenuIds;
    }
}
