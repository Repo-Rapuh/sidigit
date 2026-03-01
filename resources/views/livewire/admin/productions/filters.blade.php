<div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tahap</label>
            <select wire:model.live="filters.stage" class="form-input mt-2">
                @foreach ($this->stageOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
            <select wire:model.live="filters.status" class="form-input mt-2">
                @foreach ($this->statusOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role Assigned</label>
            <select wire:model.live="filters.assigned_role_id" class="form-input mt-2">
                <option value="">Semua Role</option>
                @foreach ($this->roleOptions as $option)
                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="button" wire:click="resetFilters" class="btn btn-secondary w-full md:w-auto">
                Reset Filter
            </button>
        </div>
    </div>
</div>
