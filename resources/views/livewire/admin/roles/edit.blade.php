<div class="space-y-6">
    <div class="flex items-baseline justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Role: {{ $name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui nama, slug, menu, dan izin role.</p>
        </div>
        <a href="{{ route('roles.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
            Kembali
        </a>
    </div>

    <div class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
        <form wire:submit.prevent="update" class="space-y-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Role</label>
                    <input type="text" id="name" wire:model.defer="name"
                        class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('name')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                    <input type="text" id="slug" wire:model.defer="slug"
                        class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('slug')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hak Akses Menu & Izin</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Perbarui akses menu beserta izinnya.</p>
                </div>

                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach ($menus as $menu)
                        <div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-950/60">
                            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4 dark:border-gray-900">
                                <div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $menu->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Menu utama & hak akses terkait.</p>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <input type="checkbox" value="{{ $menu->id }}" wire:model="menus"
                                        class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                    <span>Menu</span>
                                </label>
                            </div>

                            @if ($menu->permissions->isNotEmpty())
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Izin</p>
                                    <div class="space-y-2 rounded-2xl bg-gray-50 p-3 dark:bg-gray-900/50">
                                        @foreach ($menu->permissions as $permission)
                                            <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                                                <input type="checkbox" value="{{ $permission->id }}" wire:model="permissions"
                                                    class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                                <span>{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @foreach ($menu->children->sortBy('order') as $child)
                                <div class="rounded-2xl border border-gray-100 p-4 dark:border-gray-900">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $child->name }}</p>
                                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                            <input type="checkbox" value="{{ $child->id }}" wire:model="menus"
                                                class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                            <span>Menu</span>
                                        </label>
                                    </div>
                                    @if ($child->permissions->isNotEmpty())
                                        <div class="mt-3 space-y-2 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                            @foreach ($child->permissions as $permission)
                                                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" value="{{ $permission->id }}" wire:model="permissions"
                                                        class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                                    <span>{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-70 dark:ring-offset-gray-950">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
                <a href="{{ route('roles.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
