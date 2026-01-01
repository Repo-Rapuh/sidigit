<div class="flex flex-wrap items-end gap-4">
    <div class="flex items-center w-full gap-4">
        <div class="w-full">
            <x-forms.searchable-select label="Tipe Anggota" :options="collect($this->memberTypeOptions)
                ->map(fn($type) => ['label' => \Illuminate\Support\Str::title($type), 'value' => $type])
                ->values()" optionValue="value" optionLabel="label"
                placeholder="Semua tipe" wire:model.live="filters.member_type" />
        </div>
    </div>

    <div class="ml-auto">
        <button type="button" wire:click="resetFilters" class="btn btn-secondary">
            Reset Filter
        </button>
    </div>
</div>
