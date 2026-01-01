<div class="space-y-4">
    <x-forms.input label="Nama" name="form.name" placeholder="Nama lengkap"
        wire:model.blur="form.name" required />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-forms.input label="Username" name="form.username" placeholder="Username"
            wire:model.blur="form.username" required />
        <x-forms.input label="Email" name="form.email" placeholder="Email"
            wire:model.blur="form.email" required />
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-forms.input label="Password" name="form.password" placeholder="Password"
            wire:model.blur="form.password" type="password" @if ($showCreateModal) required @endif />
        <x-forms.input label="Konfirmasi Password" name="form.password_confirmation" placeholder="Konfirmasi Password"
            wire:model.blur="form.password_confirmation" type="password" @if ($showCreateModal) required @endif />
    </div>

    <div>
        <x-forms.searchable-select
            label="Role"
            :options="$this->availableRoles"
            placeholder="Pilih role"
            wire:model="form.role_id"
            required
        />
        @error('form.role_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
