<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;


class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $pageTitle = 'Dashboard'; // Judul default
            $currentRouteName = Route::currentRouteName();

            if ($currentRouteName) {
                // Cari menu yang route_name-nya sama persis
                $menu = Menu::where('route_name', $currentRouteName)->first();

                // Jika tidak ketemu (misal di halaman create/edit), cari berdasarkan prefix
                if (!$menu) {
                    $routePrefix = explode('.', $currentRouteName)[0];
                    // Cari menu index dari prefix tsb (misal: users.index dari users.create)
                    $menu = Menu::where('route_name', $routePrefix . '.index')->first();
                }

                if ($menu) {
                    $pageTitle = $menu->name;
                }
            }

            // Bagikan variabel $pageTitle ke semua view
            $view->with('pageTitle', $pageTitle);
        });
    }
}
