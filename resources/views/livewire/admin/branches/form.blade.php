<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-forms.input label="Nama Cabang" name="form.name" placeholder="Nama cabang"
            wire:model.blur="form.name" required />

        <x-forms.input label="No Telepon" name="form.phone" placeholder="Contoh: 08123456789"
            wire:model.blur="form.phone" />

        <x-forms.input label="Email" name="form.email" placeholder="email@perusahaan.com"
            wire:model.blur="form.email" type="email" />
    </div>

    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Alamat Cabang</label>
            <textarea wire:model.blur="form.address" rows="4" placeholder="Alamat lengkap cabang"
                class="form-input mt-3"></textarea>
            @error('form.address')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-3">
            <x-forms.image-upload label="Logo Cabang" name="form.logo" wire:model="form.logo" />
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Rekomendasi ukuran logo kotak: 64x64 atau 128x128 px. Untuk logo horizontal: 64x308 px (tinggi x lebar).
            </p>

            @if (!empty($currentLogo) && empty($form->logo))
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Logo saat ini
                    </span>
                    @php
                        $disk = \App\Support\UploadStorage::disk();
                        $logoUrl = asset('assets/tailadmin/images/logo/logo.svg');

                        try {
                            $storage = Storage::disk($disk);
                            $driver = config("filesystems.disks.{$disk}.driver");
                            $logoUrl = $driver === 's3'
                                ? $storage->temporaryUrl($currentLogo, now()->addMinutes(10))
                                : $storage->url($currentLogo);
                        } catch (\Throwable $e) {
                            $logoUrl = asset('assets/tailadmin/images/logo/logo.svg');
                        }
                    @endphp

                    <img src="{{ $logoUrl }}" alt="Logo cabang"
                        onerror="this.onerror=null;this.src='{{ asset('assets/tailadmin/images/logo/logo.svg') }}';"
                        class="h-12 w-12 rounded-xl object-contain ring-1 ring-gray-200 dark:ring-gray-700">
                </div>
            @endif
        </div>

        <div class="space-y-3">
            <x-forms.image-upload label="QRIS" name="form.qris" wire:model="form.qris" />

            @if (!empty($currentQris) && empty($form->qris))
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        QRIS saat ini
                    </span>
                    @php
                        $disk = \App\Support\UploadStorage::disk();
                        $qrisUrl = null;

                        try {
                            $storage = Storage::disk($disk);
                            $driver = config("filesystems.disks.{$disk}.driver");
                            $qrisUrl = $driver === 's3'
                                ? $storage->temporaryUrl($currentQris, now()->addMinutes(10))
                                : $storage->url($currentQris);
                        } catch (\Throwable $e) {
                            $qrisUrl = null;
                        }
                    @endphp

                    @if ($qrisUrl)
                        <img src="{{ $qrisUrl }}" alt="QRIS cabang"
                            onerror="this.onerror=null;this.remove();"
                            class="h-16 w-16 rounded-xl object-contain ring-1 ring-gray-200 dark:ring-gray-700">
                    @else
                        <span class="text-xs text-gray-500 dark:text-gray-400">QRIS belum tersedia.</span>
                    @endif
                </div>
            @endif
        </div>

        <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
            <input type="checkbox" wire:model="form.is_main"
                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500" />
            Jadikan sebagai cabang induk
        </label>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Hanya satu cabang yang dapat ditetapkan sebagai induk.
        </p>
    </div>
</div>
