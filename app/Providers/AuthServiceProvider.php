<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Secara implisit memberikan semua izin ke role Admin
        // Periksa apakah user punya role 'admin'
        Gate::before(function (User $user) {
            if ($user->roles()->where('slug', 'admin')->exists()) {
                return true;
            }
        });

        // Mendaftarkan semua permission dari database secara dinamis
        try {
            $permissions = Permission::all();
            foreach ($permissions as $permission) {
                Gate::define($permission->slug, function (User $user) use ($permission) {
                    // Cek apakah ada role dari user yang memiliki permission ini
                    return $user->roles()->whereHas('permissions', function ($query) use ($permission) {
                        $query->where('slug', $permission->slug);
                    })->exists();
                });
            }
        } catch (\Exception $e) {
            // Menangani error jika tabel permission belum ada (misalnya saat migrasi awal)
            return;
        }
    }
}
