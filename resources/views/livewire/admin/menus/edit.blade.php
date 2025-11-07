<div class="space-y-6">
    <div class="flex items-baseline justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Menu: {{ $name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui detail menu dan hirarkinya.</p>
        </div>
        <a href="{{ route('menus.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
            Kembali
        </a>
    </div>

    <div
        class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
        <form wire:submit.prevent="update" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                    Menu</label>
                <input type="text" id="name" wire:model.defer="name"
                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                @error('name')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Menu Induk</label>
                    <div class="mt-2">
                        <livewire:components.searchable-select
                            wire:key="parent-menu-select-edit-{{ $menu->id }}"
                            wire:model="parent_id"
                            :options="$parentMenuOptions"
                            placeholder="Tanpa Induk"
                            search-placeholder="Cari menu..."
                            empty-text="Menu tidak ditemukan" />
                    </div>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="route_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Route
                        Name</label>
                    <input type="text" id="route_name" wire:model.defer="route_name"
                        class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('route_name')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="icon"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ikon</label>
                    <select id="icon" wire:model="icon"
                        class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Pilih ikon</option>
                        @foreach ($icons as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('icon')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="order"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan</label>
                    <input type="number" id="order" wire:model.defer="order"
                        class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('order')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-70 dark:ring-offset-gray-950">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
                <a href="{{ route('menus.index') }}"
                    class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
