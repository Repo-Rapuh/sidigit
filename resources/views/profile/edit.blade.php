@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="space-y-6">
        <div class="flex items-baseline justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Profil Saya</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data akun dan perbarui informasi yang dibutuhkan.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div
                class="flex items-start gap-3 rounded-2xl border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 shadow-sm dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-200">
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-100">
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.666 10.667 12 5.333m-5.333 5.334-2.333-2.334" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="8" cy="8" r="6.25" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                </span>
                <div>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Lengkap
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                @class([
                                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                                ])>
                            @error('name')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Username
                            </label>
                            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                                @class([
                                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('username'),
                                ])>
                            @error('username')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                @class([
                                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('email'),
                                ])>
                            @error('email')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Password Baru
                                </label>
                                <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    Opsional
                                </span>
                            </div>
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                @class([
                                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('password'),
                                ])>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Kosongkan jika tidak ingin mengubah password.
                            </p>
                            @error('password')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••"
                                class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-950">
                        Update Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
