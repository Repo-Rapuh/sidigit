<?php

namespace App\Http\Middleware;

use App\Services\MenuCacheService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShareMenuMiddleware
{
    public function __construct(
        protected MenuCacheService $service
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            view()->share('sidebarMenus', app(MenuCacheService::class)->sidebarForUser(auth()->user()));
        }

        return $next($request);
    }
}
