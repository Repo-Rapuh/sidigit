@props([
    'customer' => null,
    'memberTypes' => [],
])

@php
    use Illuminate\Support\Str;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Pelanggan
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $customer->name ?? '') }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                ])>
            @error('name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="member_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tipe Anggota
            </label>
            <select id="member_type" name="member_type" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('member_type'),
                ])>
                <option value="" disabled {{ old('member_type', $customer?->member_type?->value) === null ? 'selected' : '' }}>
                    Pilih tipe anggota…
                </option>
                @foreach ($memberTypes as $type)
                    <option value="{{ $type }}"
                        {{ old('member_type', $customer?->member_type?->value) === $type ? 'selected' : '' }}>
                        {{ Str::title($type) }}
                    </option>
                @endforeach
            </select>
            @error('member_type')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nomor Telepon
            </label>
            <input type="text" id="phone_number" name="phone_number"
                value="{{ old('phone_number', $customer->phone_number ?? '') }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('phone_number'),
                ])>
            @error('phone_number')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Email
            </label>
            <input type="email" id="email" name="email"
                value="{{ old('email', $customer->email ?? '') }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('email'),
                ])>
            @error('email')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div>
    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Alamat
    </label>
    <textarea id="address" name="address" rows="4"
        @class([
            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('address'),
        ])>{{ old('address', $customer->address ?? '') }}</textarea>
    @error('address')
        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
    @enderror
</div>
