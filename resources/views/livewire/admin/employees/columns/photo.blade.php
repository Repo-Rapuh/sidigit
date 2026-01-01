@php
    $disk = config('filesystems.default', 'public');
    $photoUrl = asset('images/default-avatar.svg');

    if (!empty($row->photo)) {
        try {
            $driver = config("filesystems.disks.{$disk}.driver");
            $storage = Storage::disk($disk);
            $photoUrl =
                $driver === 's3'
                    ? $storage->temporaryUrl($row->photo, now()->addMinutes(10))
                    : $storage->url($row->photo);
        } catch (\Throwable $e) {
            $photoUrl = asset('images/default-avatar.svg');
        }
    }
@endphp
<div x-data="{ open: false }" class="inline-flex">
    <button type="button" @click="open = true" class="inline-flex">
        <img src="{{ $photoUrl }}" alt="Foto {{ $row->name }}"
            onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
            class="h-10 w-10 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700">
    </button>

    <div x-show="open" x-transition.opacity
        class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/70 p-4" @click="open = false"
        @keydown.escape.window="open = false">
        <div class="relative">
            <img src="{{ $photoUrl }}" alt="Foto {{ $row->name }}"
                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                class="max-h-[80vh] max-w-[90vw] rounded-2xl object-contain shadow-2xl">
        </div>
    </div>
</div>
