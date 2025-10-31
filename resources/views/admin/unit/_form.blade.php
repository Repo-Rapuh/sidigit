@php
    $unit = $unit ?? null;
@endphp

<div>
    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Nama Satuan
    </label>
    <input type="text" id="name" name="name" value="{{ old('name', optional($unit)->name) }}" required
        @class([
            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
        ])>
    @error('name')
        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
    @enderror
</div>
