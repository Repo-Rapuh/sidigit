@php
    $material = $material ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Material
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', optional($material)->name) }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                ])>
            @error('name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Kategori
            </label>
            <select id="category_id" name="category_id" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('category_id'),
                ])>
                <option value="" disabled {{ old('category_id', optional($material)->category_id) ? '' : 'selected' }}>Pilih kategori…</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) old('category_id', optional($material)->category_id) === (string) $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="reorder_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Batas Minimum (Reorder Level)
            </label>
            <input type="number" step="0.01" min="0" id="reorder_level" name="reorder_level"
                value="{{ old('reorder_level', optional($material)->reorder_level) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('reorder_level'),
                ])>
            @error('reorder_level')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="unit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Satuan
            </label>
            <select id="unit_id" name="unit_id" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('unit_id'),
                ])>
                <option value="" disabled {{ old('unit_id', optional($material)->unit_id) ? '' : 'selected' }}>Pilih satuan…</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" {{ (string) old('unit_id', optional($material)->unit_id) === (string) $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Deskripsi (Opsional)
            </label>
            <textarea id="description" name="description" rows="6"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark;border-error-400' => $errors->has('description'),
                ])>{{ old('description', optional($material)->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
