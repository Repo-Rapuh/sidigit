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

        <div class="space-y-3">
            <x-forms.image-upload label="Foto" name="form.photo" wire:model="form.photo" />

            @if (!empty($currentPhoto) && empty($form->photo))
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Foto saat ini
                    </span>
                    @php
                        $disk = config('filesystems.default', 'public');
                        $currentPhotoUrl = asset('images/default-avatar.svg');

                        if (!empty($currentPhoto)) {
                            try {
                                $driver = config("filesystems.disks.{$disk}.driver");
                                $storage = Storage::disk($disk);
                                $currentPhotoUrl = $driver === 's3'
                                    ? $storage->temporaryUrl($currentPhoto, now()->addMinutes(10))
                                    : $storage->url($currentPhoto);
                            } catch (\Throwable $e) {
                                $currentPhotoUrl = asset('images/default-avatar.svg');
                            }
                        }
                    @endphp

                    <img src="{{ $currentPhotoUrl }}" alt="Foto karyawan"
                        onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                        class="h-16 w-16 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                </div>
            @endif
        </div>
    </div>
</div>
<div>
    <livewire:components.text-editor label="Alamat" placeholder="Tulis alamat karyawan" wire:model="form.address" />
    @error('form.address')
        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
