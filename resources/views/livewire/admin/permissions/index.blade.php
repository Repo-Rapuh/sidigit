<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card :title="$title" :description="$description">
        <livewire:admin.permissions.table />
    </x-card>
</div>
