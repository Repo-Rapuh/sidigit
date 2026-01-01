<div class="space-y-4">
    @include('livewire.components.table.toolbar', ['filtersView' => $filtersView])

    @include('livewire.components.table.table')

    @include('livewire.components.table.pagination', ['paginator' => $rows])

    @if (!empty($formView))
        @include('livewire.components.modal.form')
    @endif

    @include('livewire.components.modal.delete')

    @include('livewire.components.modal.bulk-delete')
</div>
