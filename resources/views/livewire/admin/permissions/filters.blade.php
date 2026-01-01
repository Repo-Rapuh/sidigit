<div class="w-full rounded-xl border border-gray-200 bg-white p-4  dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-col gap-4 md:flex-row md:items-end">
        <div class="w-full md:flex-1">
            <x-forms.searchable-select label="Menu:" :options="$this->menuOptions" placeholder="Semua Menu"
                wire:model.live="filters.menu_id" />
        </div>

        <div class="w-full md:flex-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Urutkan berdasarkan: </label>
            <select wire:model.change="filters.created_at" class="form-input mt-2">
                <option value="desc">Terbaru</option>
                <option value="asc">Terlama</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="button" wire:click="resetFilters" class="btn btn-danger">
                Reset Filter
            </button>
        </div>
    </div>
</div>
