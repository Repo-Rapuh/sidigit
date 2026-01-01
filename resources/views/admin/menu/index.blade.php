@extends('layouts.app')

@section('title', $pageTitle ?? 'Manajemen Menu')

@section('content')
    <div class="space-y-6">
        <div class="flex items-baseline justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Daftar Menu</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola struktur navigasi yang ditampilkan pada sidebar aplikasi.
                </p>
            </div>
            @can('permission.create')
                <a href="{{ route('menus.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-900">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 4.167v11.666M4.167 10h11.666" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Tambah Menu
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div
                class="rounded-2xl border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-center font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                No.
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Nama Menu
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Induk Menu
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Route
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Ikon
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-center font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-900 dark:bg-gray-950/40">
                        @forelse ($menus as $menu)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-900/50">
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                                    {{ $loop->iteration + $menus->firstItem() - 1 }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $menu->name }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $menu->parent?->name ?? 'Induk' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    @if ($menu->route_name)
                                        <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                            {{ $menu->route_name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-600">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $menu->icon ?: '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        @can('menu.edit')
                                            <a href="{{ route('menus.edit', $menu) }}"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                                                Edit
                                            </a>
                                        @endcan
                                        @can('menu.delete')
                                            <div x-data="{ isConfirmOpen: false }" @keydown.escape.window="isConfirmOpen = false">
                                                <form x-ref="formDeleteMenu{{ $menu->id }}"
                                                    action="{{ route('menus.destroy', $menu) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" @click="isConfirmOpen = true"
                                                        class="inline-flex items-center gap-1 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-xs font-semibold text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-200 dark:hover:bg-error-500/20">
                                                        Hapus
                                                    </button>

                                                    <div x-cloak x-show="isConfirmOpen" x-transition.opacity
                                                        class="fixed inset-0 z-[100000] flex items-center justify-center bg-gray-950/90 px-4"
                                                        role="dialog" aria-modal="true" aria-labelledby="deleteMenuTitle{{ $menu->id }}"
                                                        @click.self="isConfirmOpen = false">
                                                        <div x-show="isConfirmOpen" x-transition.scale
                                                            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                                                            <div class="flex items-start gap-3">
                                                                <span
                                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-100 text-error-600 dark:bg-error-500/15 dark:text-error-300">
                                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                                        stroke="currentColor" stroke-width="1.5">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                    </svg>
                                                                </span>
                                                                <div class="space-y-2">
                                                                    <h3 id="deleteMenuTitle{{ $menu->id }}"
                                                                        class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                        Hapus Menu?
                                                                    </h3>
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                        Menghapus menu
                                                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $menu->name }}</span>
                                                                        akan mempengaruhi navigasi pengguna. Pastikan menu ini tidak lagi digunakan.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="mt-6 flex items-center justify-end gap-2">
                                                                <button type="button" @click="isConfirmOpen = false"
                                                                    class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-white">
                                                                    Batal
                                                                </button>
                                                                <button type="button"
                                                                    @click="$refs.formDeleteMenu{{ $menu->id }}.submit()"
                                                                    class="inline-flex items-center rounded-lg bg-error-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-error-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error-500 focus-visible:ring-offset-2 dark:ring-offset-gray-900">
                                                                    Ya, hapus
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Data menu belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-4 py-4 dark:border-gray-900">
                {{ $menus->links('components.pagination') }}
            </div>
        </div>
    </div>
@endsection
