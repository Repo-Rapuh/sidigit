@php
    $product = $product ?? null;
    $units = $units ?? collect();
    $selectedCategory = old('category_id', optional($product)->category_id);
    $selectedUnit = old('unit_id', optional($product)->unit_id);
    $selectedUnit = $selectedUnit !== null ? (string) $selectedUnit : null;
    $dimensionUnitNames = ['cm'];
    $unitDimensionMap = $units->mapWithKeys(function ($unit) use ($dimensionUnitNames) {
        $name = is_object($unit) ? $unit->name : ($unit['name'] ?? '');
        return [
            (string) (is_object($unit) ? $unit->id : $unit['id']) => in_array(strtolower($name), $dimensionUnitNames, true),
        ];
    });
    $showDimensionFields = $selectedUnit && ($unitDimensionMap[$selectedUnit] ?? false);
    $initialMaterialIds = $product
        ? $product->productMaterials->pluck('material_id')->map(fn ($id) => (string) $id)->toArray()
        : [];
    $selectedMaterials = collect(old('materials', $initialMaterialIds))
        ->map(fn ($id) => (string) $id)
        ->filter()
        ->values()
        ->toArray();
    $selectedCategoryKey = $selectedCategory ? (string) $selectedCategory : null;
    $materialsForSelectedCategory = $selectedCategoryKey && isset($materialsByCategory[$selectedCategoryKey])
        ? collect($materialsByCategory[$selectedCategoryKey])
            ->map(fn ($material) => [
                'id' => (string) $material['id'],
                'name' => $material['name'],
                'unit' => $material['unit'],
            ])
            ->toArray()
        : [];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-5">
            <div>
                <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    SKU
                </label>
                <input type="text" id="sku" name="sku" value="{{ old('sku', optional($product)->sku) }}" required
                    @class([
                        'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                        'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('sku'),
                    ])>
                @error('sku')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Produk
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', optional($product)->name) }}" required
                    @class([
                        'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                        'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                    ])>
                @error('name')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <label for="base_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Pokok (Rp)
                    </label>
                    <input type="number" step="0.01" min="0" id="base_price" name="base_price"
                        value="{{ old('base_price', optional($product)->base_price) }}" required
                        @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('base_price'),
                        ])>
                    @error('base_price')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Jual (Rp)
                    </label>
                    <input type="number" step="0.01" min="0" id="sale_price" name="sale_price"
                        value="{{ old('sale_price', optional($product)->sale_price) }}" required
                        @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('sale_price'),
                        ])>
                    @error('sale_price')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="unit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Satuan
                </label>
                <div id="product-unit-select" class="mt-2">
                    <select id="unit_id" name="unit_id"
                        @class([
                            'w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('unit_id'),
                        ]) required>
                        <option value=""></option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ (string) $selectedUnit === (string) $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('unit_id')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>
            <div id="dimension-fields" class="grid grid-cols-1 gap-5 lg:grid-cols-2 {{ $showDimensionFields ? '' : 'hidden' }}">
                <div>
                    <label for="length_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Panjang (cm)
                    </label>
                    <input type="number" step="0.01" min="0" id="length_cm" name="length_cm"
                        value="{{ old('length_cm', optional($product)->length_cm) }}"
                        @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('length_cm'),
                        ])>
                    @error('length_cm')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="width_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Lebar (cm)
                    </label>
                    <input type="number" step="0.01" min="0" id="width_cm" name="width_cm"
                        value="{{ old('width_cm', optional($product)->width_cm) }}"
                        @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('width_cm'),
                        ])>
                    @error('width_cm')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kategori Produk
                </label>
                <div id="product-category-select" class="mt-2">
                    <select id="category_id" name="category_id"
                        @class([
                            'w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('category_id'),
                        ]) required>
                        <option value=""></option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) $selectedCategory === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('category_id')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Bahan
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Pilih bahan berdasarkan kategori. Gunakan <kbd class="rounded border px-1 py-0.5 text-[11px]">Ctrl</kbd>/<kbd class="rounded border px-1 py-0.5 text-[11px]">Cmd</kbd> untuk memilih lebih dari satu.
                </p>

                <div class="mt-3 space-y-3">
                    <p id="materials-hint-select"
                        @class([
                            'rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-400',
                            'hidden' => $selectedCategory,
                        ])>
                        Pilih kategori terlebih dahulu untuk melihat daftar material.
                    </p>

                    <p id="materials-hint-empty"
                        @class([
                            'rounded-xl border border-dashed border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-200',
                            'hidden' => !$selectedCategory || !count($materialsForSelectedCategory),
                        ])>
                        Belum ada material pada kategori ini. Tambahkan material terlebih dahulu.
                    </p>

                    <div id="product-material-select"
                        @class(['hidden' => !$selectedCategory || !count($materialsForSelectedCategory)])>
                        <select id="materials" name="materials[]" multiple
                            @class([
                                'w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                                'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('materials'),
                            ])>
                            @foreach ($materialsForSelectedCategory as $material)
                                <option value="{{ $material['id'] }}" {{ in_array($material['id'], $selectedMaterials, true) ? 'selected' : '' }}>
                                    {{ $material['unit'] ? "{$material['name']} ({$material['unit']})" : $material['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @error('materials')
                    <p class="mt-2 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
                @error('materials.*')
                    <p class="mt-2 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Deskripsi Produk (Opsional)
                </label>
                <textarea id="description" name="description" rows="6"
                    @class([
                        'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                        'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('description'),
                    ])>{{ old('description', optional($product)->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <style>
            .select2-container--default .select2-selection--single,
            .select2-container--default .select2-selection--multiple {
                border-radius: 0.75rem;
                border: 1px solid rgba(229, 231, 235, 1);
                padding: 0.5rem 0.75rem;
                min-height: 3rem;
            }

            .dark .select2-container--default .select2-selection--single,
            .dark .select2-container--default .select2-selection--multiple {
                border-color: rgba(55, 65, 81, 1);
                background-color: rgba(17, 24, 39, 1);
                color: rgba(243, 244, 246, 1);
            }

            .select2-container--default .select2-selection__rendered {
                line-height: 1.5rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 100%;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const materialsByCategory = @json($materialsByCategory);
                const initialSelectedMaterials = @json($selectedMaterials).map(String);
                const unitDimensionMap = @json($unitDimensionMap);

                const $category = $('#category_id');
                const $unit = $('#unit_id');
                const $materials = $('#materials');
                const $materialsWrapper = $('#product-material-select');
                const $hintSelect = $('#materials-hint-select');
                const $hintEmpty = $('#materials-hint-empty');
                const $dimensionFields = $('#dimension-fields');
                const $length = $('#length_cm');
                const $width = $('#width_cm');

                const selectedMaterials = new Set(initialSelectedMaterials);

                $category.select2({
                    placeholder: 'Pilih kategori…',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#product-category-select'),
                });

                $unit.select2({
                    placeholder: 'Pilih satuan…',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#product-unit-select'),
                });

                function toggleDimensionFields(unitId, preserveValues = true) {
                    const shouldShow = unitId && unitDimensionMap[String(unitId)];
                    $dimensionFields.toggleClass('hidden', !shouldShow);
                    $length.prop('disabled', !shouldShow);
                    $width.prop('disabled', !shouldShow);

                    if (!shouldShow && !preserveValues) {
                        $length.val('');
                        $width.val('');
                    }
                }

                $materials.select2({
                    placeholder: 'Pilih material…',
                    width: '100%',
                    dropdownParent: $('#product-material-select'),
                });

                function toggleHints(categoryId, items) {
                    const hasCategory = Boolean(categoryId);
                    const hasItems = items.length > 0;

                    $hintSelect.toggleClass('hidden', hasCategory);
                    $hintEmpty.toggleClass('hidden', !(hasCategory && !hasItems));
                    $materialsWrapper.toggleClass('hidden', !hasCategory || !hasItems);

                    $materials.prop('disabled', !hasCategory || !hasItems);
                }

                function buildOption(label, value, selected = false) {
                    const option = new Option(label, value, selected, selected);
                    return option;
                }

                function populateMaterials(categoryId, preserveSelections = false) {
                    const categoryKey = categoryId ? String(categoryId) : '';
                    const materials = categoryKey && materialsByCategory[categoryKey] ? materialsByCategory[categoryKey] : [];
                    const baseSelections = preserveSelections ? new Set($materials.val() || []) : selectedMaterials;

                    $materials.empty();

                    materials.forEach((material) => {
                        const value = String(material.id);
                        const label = material.unit ? `${material.name} (${material.unit})` : material.name;
                        const isSelected = baseSelections.has(value);
                        $materials.append(buildOption(label, value, isSelected));
                    });

                    $materials.trigger('change');
                    toggleHints(categoryId, materials);
                }

                $category.on('change', function () {
                    selectedMaterials.clear();
                    const categoryId = $(this).val();
                    if (!categoryId) {
                        $materials.val(null).trigger('change');
                        toggleHints(null, []);
                        return;
                    }

                    populateMaterials(categoryId, false);
                });

                $materials.on('change', function () {
                    selectedMaterials.clear();
                    const values = $(this).val() || [];
                    values.forEach((value) => selectedMaterials.add(String(value)));
                });

                const initialCategory = $category.val();
                if (initialCategory) {
                    populateMaterials(initialCategory, true);
                } else {
                    toggleHints(null, []);
                }

                toggleDimensionFields($unit.val(), true);

                $unit.on('change', function () {
                    toggleDimensionFields($(this).val(), false);
                });
            });
        </script>
    @endpush
@endonce
