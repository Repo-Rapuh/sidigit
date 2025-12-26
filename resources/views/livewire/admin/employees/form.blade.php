<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-forms.input label="Nama Lengkap" name="form.name" placeholder="Nama lengkap" wire:model.blur="form.name"
            required />

        <div>
            <x-forms.searchable-select label="Status" :options="collect($statuses)
                ->map(fn($status) => ['label' => \Illuminate\Support\Str::title($status), 'value' => $status])
                ->values()" optionValue="value" optionLabel="label"
                placeholder="Pilih status" wire:model.blur="form.status" required />

            @error('form.status')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-forms.input label="Gaji (Rp)" name="form.salary" placeholder="0" wire:model.blur="form.salary" type="number"
            min="0" />
    </div>

    <div class="space-y-4">
        <x-forms.input label="Nomor Telepon" name="form.phone_number" placeholder="Nomor telepon"
            wire:model.blur="form.phone_number" />

        <x-forms.input label="Email" name="form.email" placeholder="Email" wire:model.blur="form.email"
            type="email" />

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Foto</label>
            <input type="file" wire:model="form.photo" accept="image/*" class="form-input mt-2" />
            @error('form.photo')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            @if (!empty($currentPhoto) && empty($form->photo))
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Foto saat ini
                    </span>
                    <img src="{{ asset('storage/' . $currentPhoto) }}" alt="Foto karyawan"
                        class="h-16 w-16 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                </div>
            @endif
        </div>
    </div>
</div>

<div>
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Alamat</label>
    <textarea rows="4" wire:model.blur="form.address" class="form-input mt-2"></textarea>
    @error('form.address')
        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
