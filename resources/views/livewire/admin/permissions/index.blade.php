<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Daftar Permission</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola izin tindakan yang terhubung ke menu.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative">
                <input type="search" wire:model.debounce.400ms="search" placeholder="Cari nama atau slug"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 pr-10 text-sm text-gray-700 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100" />
                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M9 3.5a5.5 5.5 0 1 0 3.25 10.012l3.12 3.118a.75.75 0 1 0 1.06-1.06l-3.118-3.12A5.5 5.5 0 0 0 9 3.5Zm-4 5.5a4 4 0 1 1 8 0a4 4 0 0 1-8 0Z"
                        fill="currentColor" />
                </svg>
            </div>
            @can('permission.create')
                <a href="{{ route('permissions.create') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-900">
                    Tambah Permission
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/60">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Slug</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Menu</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-900 dark:bg-gray-950/40">
                    @forelse ($permissions as $permission)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-900/50" wire:key="permission-{{ $permission->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $permission->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $permission->slug }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $permission->menu?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('permission.edit')
                                        <a href="{{ route('permissions.edit', $permission) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                                            Edit
                                        </a>
                                    @endcan
                                    @can('permission.delete')
                                        <button type="button"
                                            x-data="{}"
                                            x-on:click.prevent="if (confirm('Hapus permission {{ $permission->name }}?')) $wire.deletePermission({{ $permission->id }});"
                                            class="inline-flex items-center gap-1 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-xs font-semibold text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-200 dark:hover:bg-error-500/20">
                                            Hapus
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Data permission belum tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-4 py-4 dark:border-gray-900">
            {{ $permissions->links('components.pagination') }}
        </div>
    </div>
</div>
