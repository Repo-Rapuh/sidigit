<div class="space-y-4">
    <x-forms.input label="Nama Permission" name="form.name" placeholder="Nama permission" wire:model.blur="form.name"
        required />

    <x-forms.input label="Slug" name="form.slug" placeholder="Slug permission" wire:model.blur="form.slug" required />

    <div>
        <x-forms.searchable-select label="Menu" :options="$this->menuOptions" placeholder="Pilih Menu"
            wire:model.blur="form.menu_id" required />
        @error('form.menu_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
