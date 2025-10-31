@php
    use Illuminate\Support\Str;
@endphp

@props([
    'employee' => null,
    'statuses' => [],
])

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Lengkap
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $employee->name ?? '') }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                ])>
            @error('name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Status
            </label>
            <select id="status" name="status" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('status'),
                ])>
                <option value="" disabled {{ old('status', $employee?->status?->value) === null ? 'selected' : '' }}>
                    Pilih status…
                </option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}"
                        {{ old('status', $employee?->status?->value) === $status ? 'selected' : '' }}>
                        {{ Str::title($status) }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="salary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Gaji (Rp)
            </label>
            <input type="number" id="salary" name="salary" min="0"
                value="{{ old('salary', $employee->salary ?? '') }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('salary'),
                ])>
            @error('salary')
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
                value="{{ old('phone_number', $employee->phone_number ?? '') }}"
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
                value="{{ old('email', $employee->email ?? '') }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('email'),
                ])>
            @error('email')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Foto
            </label>
            <input type="file" id="photo" name="photo" accept="image/*"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('photo'),
                ])>
            @error('photo')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror

            @if ($employee?->photo)
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Foto saat ini
                    </span>
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="Foto {{ $employee->name }}"
                        class="h-16 w-16 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                </div>
            @endif
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
        ])>{{ old('address', $employee->address ?? '') }}</textarea>
    @error('address')
        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
    @enderror
</div>
