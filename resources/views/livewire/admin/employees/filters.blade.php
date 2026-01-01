<div class="flex flex-wrap items-end gap-4">
    <div class="flex items-center w-full gap-4">
        <div class="w-full">
            <x-forms.searchable-select label="Status" :options="collect($this->statusOptions)
                ->map(fn($status) => ['label' => \Illuminate\Support\Str::title($status), 'value' => $status])
                ->values()" optionValue="value" optionLabel="label"
                placeholder="Semua status" wire:model.live="filters.status" />
        </div>
    </div>

    <div class="ml-auto">
        <button type="button" wire:click="resetFilters" class="btn btn-secondary">
            Reset Filter
        </button>
    </div>
</div>
