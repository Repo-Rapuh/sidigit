@extends('layouts.auth')

@section('title', 'Masuk')

@section('badge', 'Secure Access')

@section('aside')
    <div class="space-y-6">
        <h2 class="text-3xl font-semibold leading-tight xl:text-4xl">
            Selamat datang kembali
        </h2>
        <p class="text-base/7 text-white/80">
            Kelola aktivitas digital Anda dengan dasbor yang kaya fitur. Semua metrik, konfigurasi peran, dan audit
            log tersedia di ujung jari Anda.
        </p>

        <div class="rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur-lg">
            <p class="text-sm/6 text-white/80">
                Tip keamanan: aktifkan autentikasi dua faktor untuk meningkatkan perlindungan akun Anda.
            </p>
        </div>

        <div class="hidden gap-4 sm:grid sm:grid-cols-2">
            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur-lg">
                <p class="text-sm font-medium text-white">Aktivitas terbaru</p>
                <p class="mt-1 text-2xl font-semibold text-white">24</p>
                <p class="text-xs text-white/80">Event dalam 24 jam terakhir</p>
            </div>
            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur-lg">
                <p class="text-sm font-medium text-white">Pengguna aktif</p>
                <p class="mt-1 text-2xl font-semibold text-white">1.2k</p>
                <p class="text-xs text-white/80">Tim yang terhubung minggu ini</p>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-lg dark:border-gray-800 dark:bg-gray-950/70">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Masuk ke akun Anda</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Gunakan email atau username yang terdaftar untuk mengakses panel admin.
            </p>
        </div>

        @error('login')
            <div
                class="flex items-start gap-3 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm font-medium text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
                <svg class="mt-0.5 h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 6v4" />
                    <path d="M10 14h.01" />
                    <path d="M10 18a8 8 0 1 0 0-16a8 8 0 0 0 0 16Z" />
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-2">
                <label for="login" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email atau Username</label>
                <div
                    class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:focus-within:border-brand-400">
                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.5 5.833 10 3l7.5 2.833v4.167c0 4.382-3.582 7.875-7.5 7.875S2.5 14.382 2.5 9.999V5.833Z" />
                        <path d="m2.5 6 7.5 3.333L17.5 6" />
                    </svg>
                    <input type="text" id="login" name="login"
                        class="w-full border-0 bg-transparent text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0 dark:text-gray-100"
                        placeholder="nama@contoh.com"
                        value="{{ old('login') }}" required autofocus>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <div
                    class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:focus-within:border-brand-400">
                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 9V7a5 5 0 0 1 10 0v2" />
                        <path
                            d="M3.5 9h13a1.5 1.5 0 0 1 1.5 1.5v5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 2 15.5v-5A1.5 1.5 0 0 1 3.5 9Z" />
                        <path d="M10 13v2" />
                    </svg>
                    <input type="password" id="password" name="password"
                        class="w-full border-0 bg-transparent text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0 dark:text-gray-100"
                        placeholder="Masukkan password" required>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember"
                        class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                    <span>Ingat saya</span>
                </label>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                        class="font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">Daftar akun</a>
                @endif
            </div>

            <button
                class="inline-flex w-full justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold tracking-wide text-white shadow-theme-sm transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/30 dark:bg-brand-500 dark:hover:bg-brand-400">
                Masuk
            </button>
        </form>
    </div>
@endsection
