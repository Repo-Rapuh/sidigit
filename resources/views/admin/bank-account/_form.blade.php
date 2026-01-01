@props(['bankAccount' => null])

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="space-y-5">
        <div>
            <label for="rekening_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nomor Rekening
            </label>
            <input type="text" id="rekening_number" name="rekening_number"
                value="{{ old('rekening_number', $bankAccount->rekening_number ?? '') }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('rekening_number'),
                ])>
            @error('rekening_number')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="account_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Pemilik Rekening
            </label>
            <input type="text" id="account_name" name="account_name"
                value="{{ old('account_name', $bankAccount->account_name ?? '') }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('account_name'),
                ])>
            @error('account_name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Bank
            </label>
            <input type="text" id="bank_name" name="bank_name"
                value="{{ old('bank_name', $bankAccount->bank_name ?? '') }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('bank_name'),
                ])>
            @error('bank_name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
