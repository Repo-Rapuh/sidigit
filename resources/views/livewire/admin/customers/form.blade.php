<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-forms.input label="Nama Pelanggan" name="form.name" placeholder="Nama pelanggan" wire:model.blur="form.name" required />

        <div>
            <x-forms.searchable-select label="Tipe Anggota" :options="collect($memberTypes)
                ->map(fn($type) => ['label' => \Illuminate\Support\Str::title($type), 'value' => $type])
                ->values()" optionValue="value" optionLabel="label"
                placeholder="Pilih tipe anggota" wire:model="form.member_type" required />
            @error('form.member_type')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-forms.input label="Nomor Telepon" name="form.phone_number" placeholder="Nomor telepon"
            wire:model.blur="form.phone_number" />
    </div>

    <div class="space-y-4">
        <x-forms.input label="Email" name="form.email" type="email" placeholder="Email"
            wire:model.blur="form.email" />

        <div>
            <livewire:components.text-editor
                label="Alamat"
                placeholder="Tulis alamat pelanggan"
                wire:model="form.address"
            />
            @error('form.address')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
