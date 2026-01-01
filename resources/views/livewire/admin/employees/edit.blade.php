<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card class="rounded-3xl p-4 shadow-theme-sm">
        <form wire:submit.prevent="update" class="space-y-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" wire:loading.attr="disabled" wire:target="update" class="btn btn-primary">
                        <span wire:loading.remove wire:target="update">Simpan</span>
                        <span wire:loading wire:target="update">Menyimpan...</span>
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>

            @include('livewire.admin.employees.form', ['statuses' => $statuses, 'currentPhoto' => $currentPhoto])
        </form>
    </x-card>
</div>
