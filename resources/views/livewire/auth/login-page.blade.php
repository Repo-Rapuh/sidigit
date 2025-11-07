<div
    class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-lg dark:border-gray-800 dark:bg-gray-950/70">
    <div class="space-y-2">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Masuk ke akun Anda</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Gunakan email atau username yang terdaftar untuk mengakses panel admin.
        </p>
    </div>

    @error('login')
        <div
            class="flex items-start gap-3 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm font-medium text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
            <svg class="mt-0.5 h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 6v4" />
                <path d="M10 14h.01" />
                <path d="M10 18a8 8 0 1 0 0-16a8 8 0 0 0 0 16Z" />
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <form wire:submit.prevent="login" class="space-y-6">
        <div class="space-y-2">
            <label for="login" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email atau
                Username</label>
            <div
                class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:focus-within:border-brand-400">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M2.5 5.833 10 3l7.5 2.833v4.167c0 4.382-3.582 7.875-7.5 7.875S2.5 14.382 2.5 9.999V5.833Z" />
                    <path d="m2.5 6 7.5 3.333L17.5 6" />
                </svg>
                <input type="text" id="username" name="username" wire:model.defer="username"
                    class="w-full border-0 bg-transparent text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0 dark:text-gray-100"
                    placeholder="nama@contoh.com" required autofocus>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-sm font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">
                        Lupa password?
                    </a>
                @endif
            </div>
            <div
                class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 dark:border-gray-800 dark:bg-gray-900 dark:focus-within:border-brand-400">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 9V7a5 5 0 0 1 10 0v2" />
                    <path
                        d="M3.5 9h13a1.5 1.5 0 0 1 1.5 1.5v5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 2 15.5v-5A1.5 1.5 0 0 1 3.5 9Z" />
                    <path d="M10 13v2" />
                </svg>
                <input type="password" id="password" name="password" wire:model.defer="password"
                    class="w-full border-0 bg-transparent text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0 dark:text-gray-100"
                    placeholder="Masukkan password" required>
            </div>
        </div>

        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="remember" wire:model="remember"
                    class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
                <span>Ingat saya</span>
            </label>

            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                    class="font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">Daftar akun</a>
            @endif
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="inline-flex w-full justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold tracking-wide text-white shadow-theme-sm transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/30 dark:bg-brand-500 dark:hover:bg-brand-400 disabled:opacity-75">
            <span wire:loading.remove>Masuk</span>
            <span wire:loading>Memproses...</span>
        </button>
    </form>
</div>
