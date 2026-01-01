<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card class="rounded-3xl p-4 shadow-theme-sm">
        <form wire:submit.prevent="save" class="space-y-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">Simpan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <x-forms.input label="Nama Role" name="name" placeholder="Nama role" wire:model.defer="name"
                    class="mt-2" required />
            </div>

            <div class="space-y-4" wire:init="loadMenuOptions">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hak Akses Menu & Izin</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Centang menu dan izin yang boleh diakses
                            role
                            ini.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" wire:model.live="selectAllMenus" @disabled(!$menuOptionsReady)
                                class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                            <span>Semua Menu</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" wire:model.live="selectAllPermissions" @disabled(!$menuOptionsReady)
                                class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                            <span>Semua Izin</span>
                        </label>
                    </div>
                </div>

                @if ($menuOptions->isEmpty())
                    <div
                        class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-950/60 dark:text-gray-400">
                        Memuat daftar menu...
                    </div>
                @else
                    <div class="grid gap-4 xl:grid-cols-2">
                        @foreach ($menuOptions as $menu)
                            @php
                                $menuLoaded = $menuPermissionsLoaded[$menu->id] ?? false;
                                $menuPerms = $menuPermissions[$menu->id] ?? [];
                            @endphp
                            <div
                                class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-950/60">
                                <div
                                    class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4 dark:border-gray-900">
                                    <div>
                                        <p class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ $menu->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Menu utama & hak akses
                                            terkait.</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label
                                            class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <input type="checkbox" value="{{ $menu->id }}" wire:model="menus"
                                                @checked(in_array($menu->id, $menus, true))
                                                class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                            <span>Menu</span>
                                        </label>
                                        <button type="button" wire:click="loadMenuPermissions({{ $menu->id }})"
                                            class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                            {{ $menuLoaded ? 'Izin dimuat' : 'Muat izin' }}
                                        </button>
                                    </div>
                                </div>

                                @if ($menuLoaded)
                                    <div class="space-y-2">
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            Izin</p>
                                        <div class="space-y-2 rounded-2xl bg-gray-50 p-3 dark:bg-gray-900/50">
                                            @forelse ($menuPerms as $permission)
                                                <label
                                                    class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" value="{{ $permission['id'] }}"
                                                        @checked(in_array($permission['id'], $permissions, true)) wire:model="permissions"
                                                        class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                                    <span>{{ $permission['name'] }}</span>
                                                </label>
                                            @empty
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada izin.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif

                                @foreach ($menu->children->sortBy('order') as $child)
                                    @php
                                        $childPerms = $childPermissions[$child->id] ?? [];
                                    @endphp
                                    <div class="rounded-2xl border border-gray-100 p-4 dark:border-gray-900">
                                        <div class="flex items-center justify-between gap-4">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $child->name }}</p>
                                            <label
                                                class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                <input type="checkbox" value="{{ $child->id }}" wire:model="menus"
                                                    @checked(in_array($child->id, $menus, true))
                                                    class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                                <span>Menu</span>
                                            </label>
                                        </div>
                                        @if ($menuLoaded)
                                            <div class="mt-3 space-y-2 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                                @forelse ($childPerms as $permission)
                                                    <label
                                                        class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                                                        <input type="checkbox" value="{{ $permission['id'] }}"
                                                            @checked(in_array($permission['id'], $permissions, true)) wire:model="permissions"
                                                            class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                                                        <span>{{ $permission['name'] }}</span>
                                                    </label>
                                                @empty
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada izin.
                                                    </p>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </form>
    </x-card>
</div>
