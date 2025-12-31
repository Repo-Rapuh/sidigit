<div class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-lg dark:border-gray-800 dark:bg-gray-950/70">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Reset password Anda</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Pastikan password baru kuat dan tidak digunakan di tempat lain.
        </p>
    </div>

    <form wire:submit.prevent="resetPassword" class="space-y-6">
        @csrf

        <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" id="email" name="email" wire:model.defer="email"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-brand-400"
                placeholder="nama@perusahaan.com" required>
            @error('email')
                <p class="text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Password baru</label>
            <input type="password" id="password" name="password" wire:model.defer="password"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-brand-400"
                placeholder="Min. 8 karakter" required>
            @error('password')
                <p class="text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="password_confirmation" class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" wire:model.defer="password_confirmation"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-brand-400"
                placeholder="Ulangi password" required>
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="inline-flex w-full justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold tracking-wide text-white shadow-theme-sm transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/30 dark:bg-brand-500 dark:hover:bg-brand-400 disabled:opacity-75">
            <span wire:loading.remove>Perbarui password</span>
            <span wire:loading>Memperbarui...</span>
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
        Kembali ke
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">
            halaman masuk
        </a>
    </p>
</div>

