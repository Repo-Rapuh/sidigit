@php
    $remainingRaw = $grandTotal - $paidAmount;
    $remaining = max(0, $grandTotal - $paidAmount);
    $change = max(0, $paidAmount - $grandTotal);
    $isPaymentLocked = $remaining <= 0;
    $paymentMethods = collect([
        ['value' => 'cash', 'label' => 'Cash'],
        ['value' => 'transfer', 'label' => 'Transfer'],
        ['value' => 'qris', 'label' => 'QRIS'],
    ]);
    $paymentsAsc = collect($payments ?? [])->sortBy('paid_at')->values();
    $latestPayment = $paymentsAsc->last();
    $runningBalance = (float) $grandTotal;
    $ledgerRows = collect([
        [
            'date' => null,
            'description' => 'Tagihan Order ' . $orderNo,
            'debit' => (float) $grandTotal,
            'credit' => 0.0,
            'balance' => max(0, $runningBalance),
        ],
    ]);

    foreach ($paymentsAsc as $payment) {
        $amount = (float) ($payment->amount ?? 0);
        $balanceBefore = $runningBalance;
        $runningBalance -= $amount;

        $changeFromPayment = max(0, $amount - max(0, $balanceBefore));
        $description = strtoupper((string) ($payment->method ?? '-'));
        if (!empty($payment->notes)) {
            $description .= ' - ' . $payment->notes;
        }
        if ($changeFromPayment > 0) {
            $description .= ' (Kembalian Rp ' . number_format($changeFromPayment, 0, ',', '.') . ')';
        }

        $ledgerRows->push([
            'date' => $payment->paid_at,
            'description' => $description,
            'debit' => 0.0,
            'credit' => $amount,
            'balance' => max(0, $runningBalance),
        ]);
    }

@endphp

<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card class="rounded-3xl p-4 shadow-theme-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.edit', ['order' => $orderId]) }}" class="btn btn-secondary">Kembali ke Edit</a>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary">Daftar Order</a>
                @if (!empty($latestPayment?->id))
                    <a href="{{ route('orders.receipt', ['order' => $orderId, 'payment_id' => $latestPayment->id, 'print' => 1]) }}" target="_blank"
                        class="btn btn-secondary inline-flex items-center gap-2">
                        <x-lucide-printer class="h-4 w-4" />
                        <span>Print Struk Terakhir</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-5">
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Order No</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $orderNo }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Status</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $status }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Grand Total</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Sisa Tagihan</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Kembalian</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">Rp {{ number_format($change, 0, ',', '.') }}</p>
            </div>
        </div>

        <form wire:submit.prevent="save" class="mt-6 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 dark:border-gray-800 dark:bg-gray-900/40">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah <span class="text-red-500">*</span></label>
                    <div class="mt-2" x-data="rupiahField(@entangle('amount').live)">
                        <input type="text" inputmode="numeric" x-model="display" @input="onInput" class="form-input"
                            required @disabled($isPaymentLocked) />
                    </div>
                    <button type="button" wire:click="fillRemainingAmount" class="btn btn-secondary mt-2 w-full"
                        @disabled($isPaymentLocked)>
                        Lunas
                    </button>
                    @error('amount')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-forms.searchable-select
                        label="Metode"
                        :options="$paymentMethods"
                        optionValue="value"
                        optionLabel="label"
                        placeholder="Pilih metode"
                        wire:model="method"
                    />
                    @error('method')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Bayar <span class="text-red-500">*</span></label>
                    <input type="datetime-local" wire:model="paid_at"
                        class="form-input mt-2" required />
                    @error('paid_at')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                    <input type="text" wire:model="notes" class="form-input mt-2" />
                    @error('notes')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save"
                    @disabled($isPaymentLocked)>
                    <span wire:loading.remove wire:target="save">Simpan Pembayaran</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 dark:border-gray-800 dark:bg-gray-900/40">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Pembayaran</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2">No</th>
                            <th class="py-2">Tanggal</th>
                            <th class="py-2">Keterangan</th>
                            <th class="py-2 text-right">Debit</th>
                            <th class="py-2 text-right">Kredit</th>
                            <th class="py-2 text-right">Sisa Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($ledgerRows as $row)
                            <tr>
                                <td class="py-2 text-gray-700 dark:text-gray-200">{{ $loop->iteration }}</td>
                                <td class="py-2 text-gray-900 dark:text-gray-100">
                                    @if ($row['date'])
                                        {{ \Illuminate\Support\Carbon::parse($row['date'])->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2 text-gray-700 dark:text-gray-200">{{ $row['description'] }}</td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-200">
                                    @if ($row['debit'] > 0)
                                        Rp {{ number_format((float) $row['debit'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-200">
                                    @if ($row['credit'] > 0)
                                        Rp {{ number_format((float) $row['credit'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                    Rp {{ number_format((float) $row['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500">Belum ada transaksi pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-gray-200 dark:border-gray-800">
                        <tr>
                            <td colspan="3" class="py-2 font-semibold text-gray-700 dark:text-gray-200">Total</td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                Rp {{ number_format($paidAmount, 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                Rp {{ number_format($remaining, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if ($change > 0)
                <p class="mt-3 text-sm text-amber-700 dark:text-amber-300">
                    Kembalian saat ini: <strong>Rp {{ number_format($change, 0, ',', '.') }}</strong>
                </p>
            @endif
            @if ($remainingRaw <= 0)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Tagihan sudah tertutup. Jika total bayar melebihi tagihan, selisih minus otomatis dicatat sebagai kembalian.
                </p>
            @endif
        </div>
    </x-card>
</div>
