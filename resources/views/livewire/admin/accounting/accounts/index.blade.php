<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card :title="$title" :description="$description">
        <livewire:admin.accounting.accounts.table />
    </x-card>
</div>

