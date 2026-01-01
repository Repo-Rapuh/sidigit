<div class="flex flex-wrap items-end gap-4">
    <div class="flex items-center w-full gap-4">
        <div class="w-full">
            <x-forms.searchable-select label="Objek" :options="collect($this->logNameOptions)->map(fn($name) => ['label' => $name, 'value' => $name])->values()" optionValue="value" optionLabel="label"
                placeholder="Semua objek" wire:model.live="filters.log_name" />
        </div>
        <div class="w-full">
            <x-forms.searchable-select label="User" :options="$this->userOptions" optionValue="id" optionLabel="label"
                placeholder="Semua user" wire:model.live="filters.causer_id" />
        </div>
    </div>

    <div class="ml-auto">
        <button type="button" wire:click="resetFilters" class="btn btn-secondary">
            Reset Filter
        </button>
    </div>
</div>
