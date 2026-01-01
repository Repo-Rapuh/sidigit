@php
    $warehouse = $warehouse ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Gudang
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', optional($warehouse)->name) }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                ])>
            @error('name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Deskripsi (Opsional)
            </label>
            <textarea id="description" name="description" rows="6"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('description'),
                ])>{{ old('description', optional($warehouse)->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="location_lat" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Latitude (koordinat)
            </label>
            <input type="number" step="0.0000001" id="location_lat" name="location_lat"
                value="{{ old('location_lat', optional($warehouse)->location_lat) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark;border-error-400' => $errors->has('location_lat'),
                ])>
            @error('location_lat')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="location_lng" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Longitude (koordinat)
            </label>
            <input type="number" step="0.0000001" id="location_lng" name="location_lng"
                value="{{ old('location_lng', optional($warehouse)->location_lng) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark;border-error-400' => $errors->has('location_lng'),
                ])>
            @error('location_lng')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Klik lokasi pada peta di bawah untuk mengisi koordinat secara otomatis.
            </p>
        </div>
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Pilih Lokasi di Peta
    </label>
    <div id="warehouse-map" class="mt-2 h-80 w-full overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800"></div>
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
        Gunakan wheel mouse atau gesture untuk zoom, lalu klik lokasi gudang. Titik merah dapat digeser ulang jika diperlukan.
    </p>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-o9N1j7kGStjzsP1S1U1Jqq9Lt7GkNStG3b59hK5Y+90=" crossorigin="" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mapElement = document.getElementById('warehouse-map');
                if (!mapElement) {
                    return;
                }

                const latInput = document.getElementById('location_lat');
                const lngInput = document.getElementById('location_lng');

                const defaultLat = parseFloat(latInput?.value ?? '') || -1.1541;
                const defaultLng = parseFloat(lngInput?.value ?? '') || 116.2825;
                const hasInitialPosition = !!(latInput?.value && lngInput?.value);
                const map = L.map(mapElement).setView([defaultLat, defaultLng], hasInitialPosition ? 13 : 5);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
                }).addTo(map);

                let marker = null;

                const setMarker = (lat, lng) => {
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        marker.on('dragend', function (event) {
                            const position = event.target.getLatLng();
                            latInput.value = position.lat.toFixed(7);
                            lngInput.value = position.lng.toFixed(7);
                        });
                    }
                };

                if (hasInitialPosition) {
                    setMarker(defaultLat, defaultLng);
                }

                requestAnimationFrame(() => map.invalidateSize());

                map.on('click', function (event) {
                    const { lat, lng } = event.latlng;
                    if (latInput) {
                        latInput.value = lat.toFixed(7);
                    }
                    if (lngInput) {
                        lngInput.value = lng.toFixed(7);
                    }
                    setMarker(lat, lng);
                });

                if (latInput && lngInput) {
                    const syncFromInputs = () => {
                        const lat = parseFloat(latInput.value);
                        const lng = parseFloat(lngInput.value);
                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            setMarker(lat, lng);
                            map.setView([lat, lng], map.getZoom() < 13 ? 13 : map.getZoom());
                        }
                    };

                    latInput.addEventListener('change', syncFromInputs);
                    lngInput.addEventListener('change', syncFromInputs);
                }
            });
        </script>
    @endpush
@endonce
