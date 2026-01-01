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
                        <span wire:loading.remove wire:target="save">Update Profil</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <x-forms.input label="Nama Lengkap" name="name" placeholder="Nama lengkap"
                        wire:model.blur="name" required />

                    <x-forms.input label="Username" name="username" placeholder="Username"
                        wire:model.blur="username" required />

                    <x-forms.input label="Email" name="email" placeholder="Email"
                        wire:model.blur="email" type="email" required />
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Password Baru
                            </label>
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                Opsional
                            </span>
                        </div>
                        <input type="password" wire:model.blur="password" placeholder="••••••••"
                            class="form-input mt-2" />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Kosongkan jika tidak ingin mengubah password.
                        </p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Konfirmasi Password Baru
                        </label>
                        <input type="password" wire:model.blur="password_confirmation" placeholder="••••••••"
                            class="form-input mt-2" />
                    </div>
                </div>
            </div>
        </form>
    </x-card>
</div>
