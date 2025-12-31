<div class="space-y-6">
    <div class="flex items-baseline justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tambah User Baru</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lengkapi detail akun dan pilih peran yang sesuai.</p>
        </div>
        <a href="{{ route('users.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
            Kembali
        </a>
    </div>

    <div class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <input type="text" id="name" wire:model.defer="name"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        @error('name')
                            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="username" class="text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                        <input type="text" id="username" wire:model.defer="username"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        @error('username')
                            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" id="email" wire:model.defer="email"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        @error('email')
                            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" id="password" wire:model.defer="password"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        @error('password')
                            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" wire:model.defer="password_confirmation"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    </div>
                    <div>
                        <label for="roles" class="text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                        <select id="roles" multiple wire:model="roles"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            @foreach ($availableRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Tekan Ctrl/Cmd untuk memilih lebih dari satu peran.</p>
                        @error('roles')
                            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-70 dark:ring-offset-gray-950">
                    <span wire:loading.remove>Simpan</span>
                    <span wire:loading>Memproses...</span>
                </button>
                <a href="{{ route('users.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
