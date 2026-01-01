{{-- Modal Component --}}
<x-modal wire:model="showFormModal" :maxWidth="$modalMaxWidth">
    <x-slot name="header">
        {{ $modalTitle }}
    </x-slot>

    @if (!empty($formView))
        @include($formView)
    @endif

    <x-slot name="footer">
        <button wire:click="closeModal" class="btn btn-secondary">
            {{ $modalCancelLabel }}
        </button>

        <button wire:click="{{ $modalActionMethod }}" wire:loading.attr="disabled" wire:target="{{ $modalActionMethod }}"
            class="btn btn-primary">
            <span wire:loading.remove wire:target="{{ $modalActionMethod }}">
                {{ $modalActionLabel }}
            </span>

            <span wire:loading wire:target="{{ $modalActionMethod }}">
                Saving...
            </span>
        </button>
    </x-slot>
</x-modal>
