<x-modal wire:model="showDeleteModal" :maxWidth="$modalMaxWidth">
    <x-slot name="header">
        <span class="text-red-600 dark:text-red-400">Confirm Delete</span>
    </x-slot>

    <p class="text-sm text-gray-600 dark:text-gray-300">
        Are you sure you want to delete this data?
    </p>

    <x-slot name="footer">
        <button wire:click="closeModal" class="btn btn-secondary">
            Cancel
        </button>

        <button wire:click="delete" class="btn btn-danger">
            Delete
        </button>
    </x-slot>
</x-modal>
