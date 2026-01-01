<x-modal wire:model="showBulkDeleteModal" :maxWidth="$modalMaxWidth">
    <x-slot name="header">
        <span class="text-red-600 dark:text-red-400">Confirm Bulk Delete</span>
    </x-slot>

    <p class="text-sm text-gray-600 dark:text-gray-300">
        Are you sure you want to delete {{ count($selected) }} selected items?
    </p>

    <x-slot name="footer">
        <button wire:click="closeModal" class="btn btn-secondary">
            Cancel
        </button>

        <button wire:click="bulkDelete" class="btn btn-danger">
            Delete Selected
        </button>
    </x-slot>
</x-modal>
