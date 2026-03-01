<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-forms.input label="Kode Akun" name="form.code" placeholder="Contoh: 1001" wire:model.blur="form.code" required />
        <x-forms.input label="Nama Akun" name="form.name" placeholder="Contoh: Kas" wire:model.blur="form.name" required />
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                Tipe Akun <span class="text-red-500">*</span>
            </label>
            <select wire:model.live="form.type" class="form-input mt-3">
                <option value="asset">Asset</option>
                <option value="liability">Liability</option>
                <option value="equity">Equity</option>
                <option value="revenue">Revenue</option>
                <option value="expense">Expense</option>
            </select>
            @error('form.type')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                Saldo Normal <span class="text-red-500">*</span>
            </label>
            <select wire:model.live="form.normal_balance" class="form-input mt-3">
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
            </select>
            @error('form.normal_balance')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
            <input type="checkbox" wire:model.live="form.is_active"
                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500 dark:focus:ring-brand-400/30" />
            <span>Akun aktif</span>
        </label>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Catatan</label>
        <textarea wire:model.blur="form.notes" rows="3" class="form-input mt-3" placeholder="Catatan akun (opsional)"></textarea>
        @error('form.notes')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

