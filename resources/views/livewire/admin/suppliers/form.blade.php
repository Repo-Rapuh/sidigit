<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-forms.input label="Nama Supplier" name="form.name" placeholder="Nama supplier"
            wire:model.blur="form.name" required />

        <x-forms.input label="Atas Nama (Opsional)" name="form.on_behalf" placeholder="Atas nama"
            wire:model.blur="form.on_behalf" />

        <x-forms.input label="Industri" name="form.industry" placeholder="Industri"
            wire:model.blur="form.industry" required />

        <x-forms.input label="Nomor Telepon" name="form.phone_number" placeholder="Nomor telepon"
            wire:model.blur="form.phone_number" required />
    </div>

    <div class="space-y-4">
        <x-forms.input label="Email (Opsional)" name="form.email" placeholder="Email"
            wire:model.blur="form.email" type="email" />

        <x-forms.input label="Nomor Rekening (Opsional)" name="form.rekening_number" placeholder="Nomor rekening"
            wire:model.blur="form.rekening_number" />

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Alamat (Opsional)</label>
            <textarea rows="4" wire:model.blur="form.address" class="form-input mt-2"></textarea>
            @error('form.address')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
