<div class="space-y-4">
    <x-forms.input label="Nama Menu" name="form.name" placeholder="Nama menu"
        wire:model.blur="form.name" required />

    <x-forms.searchable-select
        label="Menu Induk"
        :options="$this->parentMenuOptions"
        placeholder="Tanpa Induk"
        wire:model="form.parent_id"
    />
    @error('form.parent_id')
        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <x-forms.input label="Route Name" name="form.route_name" placeholder="Route name"
        wire:model.blur="form.route_name" />

    <div class="space-y-2">
        <x-forms.input
            label="Ikon"
            name="form.icon"
            placeholder="Contoh: dashboard atau bi bi-receipt"
            wire:model.blur="form.icon"
        />
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Isi key ikon dari preset atau class ikon custom.
        </p>
        <x-forms.searchable-select
            label="Preset Ikon (Opsional)"
            :options="$this->iconOptions"
            placeholder="Pilih preset"
            wire:model="form.icon"
        />
        @error('form.icon')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <x-forms.input label="Urutan" name="form.order" placeholder="0"
        wire:model.blur="form.order" type="number" required />
</div>
