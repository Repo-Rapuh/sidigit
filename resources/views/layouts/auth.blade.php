<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageTitleSection = $__env->yieldContent('title');
        $pageTitle = $title ?? ($pageTitleSection !== '' ? $pageTitleSection : null);
    @endphp
    <title>{{ $pageTitle ?? config('app.name', 'TailAdmin') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>

<body
    class="min-h-screen bg-gray-50 font-sans text-gray-700 antialiased transition-colors duration-200 dark:bg-gray-900 dark:text-gray-200">
    <div class="flex min-h-screen w-full">
        <div
            class="relative hidden flex-1 flex-col justify-between overflow-hidden border-r border-gray-200 bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 px-10 py-12 text-white dark:border-gray-800 dark:from-gray-900 dark:via-gray-900 dark:to-black lg:flex">
            <div class="flex items-center justify-between">
                <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/tailadmin/images/logo/logo-icon.svg') }}" class="h-10 w-10"
                        alt="Logo icon">
                    <span
                        class="text-lg font-semibold tracking-wide lg:text-xl">{{ config('app.name', 'TailAdmin') }}</span>
                </a>
                @php
                    $badgeSection = trim($__env->yieldContent('badge'));
                    $badgeLabel = $badge ?? ($badgeSection !== '' ? $badgeSection : null);
                @endphp
                <div
                    class="hidden rounded-full border border-white/30 px-4 py-1.5 text-sm/6 font-medium tracking-wide lg:flex">
                    {{ $badgeLabel ?? 'Admin Portal' }}
                </div>
            </div>

            <div class="mx-auto max-w-lg">
                @isset($aside)
                    {!! $aside !!}
                @else
                    @yield('aside')
                @endisset
            </div>

            <div class="flex items-center justify-between text-sm/6 text-white/70">
                <span>&copy; {{ now()->year }} {{ config('app.name', 'TailAdmin') }}</span>
                <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}"
                    class="inline-flex items-center gap-2 text-white transition hover:text-white/90">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.333 3.333h13.334v13.334H3.333V3.333Z" />
                        <path d="m3.333 5.833 6.334 4.167 6.666-4.167" />
                    </svg>
                    Hubungi kami
                </a>
            </div>
        </div>

        <main class="flex w-full flex-1 items-center justify-center px-6 py-10 sm:px-8 lg:px-16">
            <div class="w-full max-w-md space-y-8">
                <div class="flex items-center gap-3 text-brand-600 dark:text-brand-400 lg:hidden">
                    <img src="{{ asset('assets/tailadmin/images/logo/logo-icon.svg') }}" class="h-10 w-10"
                        alt="Logo icon">
                    <span class="text-lg font-semibold tracking-wide">{{ config('app.name', 'TailAdmin') }}</span>
                </div>
                @isset($slot)
                    {{ $slot }}
                @endisset
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
