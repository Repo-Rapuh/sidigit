@php
    $hasCategory = !empty($category_id);
    $hasMaterials = count($materialsForSelectedCategory ?? []) > 0;
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-5">
            <div>
                <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    SKU
                </label>
                <input type="text" id="sku" wire:model.defer="sku" required placeholder="SKU produk" @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                        'sku'),
                ])>
                @error('sku')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Produk
                </label>
                <input type="text" id="name" wire:model.defer="name" required placeholder="Nama produk" @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                        'name'),
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
                    <input type="number" step="0.01" min="0" id="base_price" wire:model.defer="base_price" placeholder="0"
                        required @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                'base_price'),
                        ])>
                    @error('base_price')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Jual (Rp)
                    </label>
                    <input type="number" step="0.01" min="0" id="sale_price" wire:model.defer="sale_price" placeholder="0"
                        required @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                'sale_price'),
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
                    <select id="unit_id" wire:model="unit_id" @class([
                        'w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                        'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                            'unit_id'),
                    ]) required>
                        <option value="">Pilih satuan</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @error('unit_id')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div id="dimension-fields" @class([
                'grid grid-cols-1 gap-5 lg:grid-cols-2',
                'hidden' => !$showDimensionFields,
            ])>
                <div>
                    <label for="length_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Panjang (cm)
                    </label>
                    <input type="number" step="0.01" min="0" id="length_cm" wire:model.defer="length_cm" placeholder="0"
                        @disabled(!$showDimensionFields) @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                'length_cm'),
                        ])>
                    @error('length_cm')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="width_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Lebar (cm)
                    </label>
                    <input type="number" step="0.01" min="0" id="width_cm" wire:model.defer="width_cm" placeholder="0"
                        @disabled(!$showDimensionFields) @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                'width_cm'),
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
                    <select id="category_id" wire:model="category_id" @class([
                        'w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                        'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                            'category_id'),
                    ]) required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
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
                    Pilih bahan berdasarkan kategori. Gunakan <kbd
                        class="rounded border px-1 py-0.5 text-[11px]">Ctrl</kbd>/<kbd
                        class="rounded border px-1 py-0.5 text-[11px]">Cmd</kbd> untuk memilih lebih dari satu.
                </p>

                <div class="mt-3 space-y-3">
                    <p id="materials-hint-select" @class([
                        'rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-400',
                        'hidden' => $hasCategory,
                    ])>
                        Pilih kategori terlebih dahulu untuk melihat daftar material.
                    </p>

                    <p id="materials-hint-empty" @class([
                        'rounded-xl border border-dashed border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-200',
                        'hidden' => !$hasCategory || $hasMaterials,
                    ])>
                        Belum ada material pada kategori ini. Tambahkan material terlebih dahulu.
                    </p>

                    <div id="product-material-select" @class(['hidden' => !$hasCategory || !$hasMaterials])>
                        @php
                            $materialOptions = collect($materialsForSelectedCategory ?? [])->map(function ($material) {
                                return [
                                    'id' => $material['id'],
                                    'label' => $material['unit']
                                        ? $material['name'] . ' (' . $material['unit'] . ')'
                                        : $material['name'],
                                ];
                            })->values()->all();
                        @endphp
                        <livewire:components.searchable-multi-select
                            :options="$materialOptions"
                            optionLabelKey="label"
                            optionValueKey="id"
                            placeholder="Pilih material"
                            wire:model="materials"
                            :required="true"
                        />
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
                <livewire:components.text-editor
                    label="Deskripsi Produk (Opsional)"
                    placeholder="Tulis deskripsi produk"
                    wire:model="product_description"
                />
                @error('product_description')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
