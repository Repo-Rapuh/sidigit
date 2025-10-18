@extends('layouts.app')

@section('title', 'Tambah Menu')

@section('content')
    <div class="space-y-6">
        <div class="flex items-baseline justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tambah Menu Baru</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Definisikan menu baru beserta hierarki dan urutan tampilnya.
                </p>
            </div>
            <a href="{{ route('menus.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                Kembali
            </a>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
            <form action="{{ route('menus.store') }}" method="POST" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Menu
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                required>
                            @error('name')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Induk Menu (Parent)
                            </label>
                            <select id="parent_id" name="parent_id"
                                class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Tidak ada induk</option>
                                @foreach ($parentMenus as $parent)
                                    <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="route_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Route Name
                            </label>
                            <input type="text" id="route_name" name="route_name" value="{{ old('route_name') }}"
                                class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Kosongkan jika menu ini adalah induk yang tidak memiliki route.
                            </p>
                            @error('route_name')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ikon
                            </label>
                            <select id="icon" name="icon"
                                class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Tanpa ikon</option>
                                @foreach ($icons as $icon)
                                    <option value="{{ $icon }}" @selected(old('icon') == $icon)>{{ $icon }}</option>
                                @endforeach
                            </select>
                            @error('icon')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Urutan Tampil
                            </label>
                            <input type="number" id="order" name="order" value="{{ old('order', 0) }}"
                                class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                required>
                            @error('order')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-950">
                        Simpan Menu
                    </button>
                    <a href="{{ route('menus.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
