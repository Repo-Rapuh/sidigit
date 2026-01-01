<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([ // <-- Cari atau tambahkan method ini
        // ... provider lain jika ada
        // App\Providers\ViewServiceProvider::class, // <-- Daftarkan di sini
        App\Providers\AuthServiceProvider::class,
        RichanFongdasen\EloquentBlameable\ServiceProvider::class,

    ])
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->use([
             \App\Http\Middleware\ShareMenuMiddleware::class
         ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
