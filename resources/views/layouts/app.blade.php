<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'TailAdmin'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    }"
    x-init="$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{ 'dark bg-gray-900': darkMode === true }"
    class="text-gray-700 antialiased">

    @includeIf('layouts.partials.preloader')

    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
        @include('layouts.partials.sidebar')

        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
            @includeIf('layouts.partials.overlay')
            @include('layouts.partials.header')

            <main class="flex-1">
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                    @yield('content')
                </div>
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
