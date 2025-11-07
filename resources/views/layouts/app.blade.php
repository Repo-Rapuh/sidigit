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
    <script>
        window.deferLoadingAlpine = function (callback) {
            window.addEventListener('DOMContentLoaded', () => callback());
        };
    </script>
    @livewireStyles
    @stack('styles')
</head>

@php
    $currentRoute = Route::currentRouteName();
@endphp

<body x-data="{
    page: @js($currentRoute),
    loaded: true,
    darkMode: JSON.parse(localStorage.getItem('darkMode') ?? 'false'),
    stickyMenu: false,
    sidebarToggle: false,
    scrollTop: false,
}" x-init="$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{ 'dark bg-gray-900': darkMode === true }"
    class="text-gray-700 antialiased">

    <livewire:layout.preloader />

    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
        <livewire:layout.sidebar />

        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
            <livewire:layout.overlay />
            <livewire:layout.header />

            <main class="flex-1">
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </main>

            <livewire:layout.footer />
        </div>
    </div>

    @stack('scripts')
    @livewireScripts
</body>

</html>
