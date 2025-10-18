@extends('layouts.app')

@section('title', 'Tambah Permission')

@section('content')
    <div class="space-y-6">
        <div class="flex items-baseline justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tambah Permission Baru</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Permission menentukan aksi apa saja yang diizinkan untuk sebuah role.
                </p>
            </div>
            <a href="{{ route('permissions.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                Kembali
            </a>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
            <form action="{{ route('permissions.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Permission
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        required>
                    @error('name')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Slug
                    </label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                        class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        required>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Gunakan format <code>resource.aksi</code> (contoh: <span class="font-mono">user.view</span>).
                    </p>
                    @error('slug')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="menu_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Induk Menu
                    </label>
                    <select id="menu_id" name="menu_id"
                        class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Tidak terikat menu</option>
                        @foreach ($menus as $menu)
                            <option value="{{ $menu->id }}" @selected(old('menu_id') == $menu->id)>
                                {{ $menu->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Mengelompokkan izin berdasarkan menu memudahkan pencarian dan manajemen.
                    </p>
                    @error('menu_id')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-950">
                        Simpan Permission
                    </button>
                    <a href="{{ route('permissions.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
