<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-forms.input label="Nama Gudang" name="form.name" placeholder="Nama gudang"
            wire:model.blur="form.name" />

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Deskripsi</label>
            <textarea rows="5" wire:model.blur="form.description" class="form-input mt-2"></textarea>
            @error('form.description')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-4">
        <x-forms.input label="Latitude" name="form.location_lat" placeholder="-1.1541000"
            wire:model.blur="form.location_lat" type="number" step="0.0000001" id="location_lat" />

        <x-forms.input label="Longitude" name="form.location_lng" placeholder="116.2825000"
            wire:model.blur="form.location_lng" type="number" step="0.0000001" id="location_lng" />

        <p class="text-xs text-gray-500 dark:text-gray-400">
            Klik lokasi pada peta untuk mengisi koordinat secara otomatis.
        </p>
    </div>
</div>

<div class="mt-6" wire:ignore>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
        Pilih Lokasi di Peta
    </label>
    <div id="warehouse-map"
        class="mt-2 h-80 w-full overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800"></div>
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
        Gunakan zoom lalu klik lokasi gudang. Titik bisa digeser ulang.
    </p>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-o9N1j7kGStjzsP1S1U1Jqq9Lt7GkNStG3b59hK5Y+90=" crossorigin="" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const initWarehouseMap = () => {
                const mapElement = document.getElementById('warehouse-map');
                if (!mapElement || mapElement.dataset.mapReady === '1') {
                    return;
                }
                if (!window.L) {
                    setTimeout(initWarehouseMap, 100);
                    return;
                }

                mapElement.dataset.mapReady = '1';

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

                const syncInput = (input, value) => {
                    if (!input) return;
                    input.value = value;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                };

                const setMarker = (lat, lng) => {
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        marker.on('dragend', function (event) {
                            const position = event.target.getLatLng();
                            syncInput(latInput, position.lat.toFixed(7));
                            syncInput(lngInput, position.lng.toFixed(7));
                        });
                    }
                };

                if (hasInitialPosition) {
                    setMarker(defaultLat, defaultLng);
                }

                requestAnimationFrame(() => map.invalidateSize());

                map.on('click', function (event) {
                    const { lat, lng } = event.latlng;
                    syncInput(latInput, lat.toFixed(7));
                    syncInput(lngInput, lng.toFixed(7));
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
            };

            document.addEventListener('DOMContentLoaded', initWarehouseMap);
            document.addEventListener('livewire:navigated', initWarehouseMap);
        </script>
    @endpush
@endonce
