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

    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Ikon</label>
        <select wire:model.defer="form.icon" class="form-input mt-2">
            <option value="">Pilih ikon</option>
            @foreach ($this->iconOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.icon')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <x-forms.input label="Urutan" name="form.order" placeholder="0"
        wire:model.blur="form.order" type="number" required />
</div>
