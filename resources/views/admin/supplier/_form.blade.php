@php
    $supplier = $supplier ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Supplier
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', optional($supplier)->name) }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                ])>
            @error('name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="on_behalf" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Atas Nama (Opsional)
            </label>
            <input type="text" id="on_behalf" name="on_behalf" value="{{ old('on_behalf', optional($supplier)->on_behalf) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('on_behalf'),
                ])>
            @error('on_behalf')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="industry" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Industri
            </label>
            <input type="text" id="industry" name="industry" value="{{ old('industry', optional($supplier)->industry) }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('industry'),
                ])>
            @error('industry')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nomor Telepon
            </label>
            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', optional($supplier)->phone_number) }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('phone_number'),
                ])>
            @error('phone_number')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Email (Opsional)
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', optional($supplier)->email) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('email'),
                ])>
            @error('email')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="rekening_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nomor Rekening (Opsional)
            </label>
            <input type="text" id="rekening_number" name="rekening_number" value="{{ old('rekening_number', optional($supplier)->rekening_number) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('rekening_number'),
                ])>
            @error('rekening_number')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div>
    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Alamat (Opsional)
    </label>
    <textarea id="address" name="address" rows="4"
        @class([
            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('address'),
        ])>{{ old('address', optional($supplier)->address) }}</textarea>
    @error('address')
        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
    @enderror
</div>
