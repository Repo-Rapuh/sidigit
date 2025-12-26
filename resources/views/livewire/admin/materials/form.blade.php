<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-forms.input label="Nama Material" name="form.name" placeholder="Nama material"
            wire:model.blur="form.name" required />

        <div>
            <x-forms.searchable-select
                label="Kategori"
                :options="$this->categoryOptions"
                placeholder="Pilih kategori"
                wire:model="form.category_id"
                required
            />
            @error('form.category_id')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-forms.input label="Batas Minimum (Reorder Level)" name="form.reorder_level" placeholder="0"
            wire:model.blur="form.reorder_level" type="number" step="0.01" min="0" />
    </div>

    <div class="space-y-4">
        <div>
            <x-forms.searchable-select
                label="Satuan"
                :options="$this->unitOptions"
                placeholder="Pilih satuan"
                wire:model="form.unit_id"
                required
            />
            @error('form.unit_id')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <livewire:components.text-editor
                label="Deskripsi"
                placeholder="Tulis deskripsi material"
                wire:model="form.description"
            />
            @error('form.description')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
