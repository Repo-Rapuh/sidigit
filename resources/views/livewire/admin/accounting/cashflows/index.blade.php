<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/60">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Periode</label>
                <select wire:model.live="period" class="form-input mt-3">
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Sumber</label>
                <select wire:model.live="source" class="form-input mt-3">
                    <option value="all">Semua</option>
                    <option value="payment">Pemasukan Saja</option>
                    <option value="expense">Pengeluaran Saja</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Metode</label>
                <select wire:model.live="method" class="form-input mt-3">
                    <option value="all">Semua Metode</option>
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Dari</label>
                <input type="date" wire:model.live="start_date" class="form-input mt-3" @disabled($period !== 'custom') />
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Sampai</label>
                <input type="date" wire:model.live="end_date" class="form-input mt-3" @disabled($period !== 'custom') />
            </div>
        </div>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Menampilkan mutasi periode {{ \Carbon\Carbon::parse($range['start_date'])->format('d/m/Y') }} s/d
            {{ \Carbon\Carbon::parse($range['end_date'])->format('d/m/Y') }}.
        </p>
    </div>

    <x-card :title="$title" :description="$description">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Saldo Awal</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format((float) $summary['opening_balance'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/20">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total Masuk</p>
                <p class="mt-1 text-lg font-semibold text-emerald-700 dark:text-emerald-300">
                    Rp {{ number_format((float) $summary['cash_in'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-900/40 dark:bg-rose-900/20">
                <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">Total Keluar</p>
                <p class="mt-1 text-lg font-semibold text-rose-700 dark:text-rose-300">
                    Rp {{ number_format((float) $summary['cash_out'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Net Cashflow</p>
                <p class="mt-1 text-lg font-semibold {{ $summary['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    Rp {{ number_format((float) $summary['net'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Saldo Akhir</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format((float) $summary['closing_balance'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </x-card>

    <x-card title="Mutasi Arus Kas" description="Pemasukan dan pengeluaran digabung dengan saldo berjalan.">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Sumber</th>
                        <th class="px-3 py-2">Referensi</th>
                        <th class="px-3 py-2">Keterangan</th>
                        <th class="px-3 py-2">Metode</th>
                        <th class="px-3 py-2 text-right">Masuk</th>
                        <th class="px-3 py-2 text-right">Keluar</th>
                        <th class="px-3 py-2 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr class="bg-gray-50/70 dark:bg-gray-900/40">
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300" colspan="7">Saldo awal periode</td>
                        <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format((float) $summary['opening_balance'], 0, ',', '.') }}
                        </td>
                    </tr>

                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                @if ($row['source'] === 'payment')
                                    <span class="inline-flex rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Pemasukan
                                    </span>
                                @else
                                    <span class="inline-flex rounded-md bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                        Pengeluaran
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $row['reference'] }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['description'] }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 uppercase">{{ $row['method'] }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-300 whitespace-nowrap">
                                {{ (float) $row['cash_in'] > 0 ? 'Rp ' . number_format((float) $row['cash_in'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right text-rose-700 dark:text-rose-300 whitespace-nowrap">
                                {{ (float) $row['cash_out'] > 0 ? 'Rp ' . number_format((float) $row['cash_out'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                Rp {{ number_format((float) $row['balance'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                Belum ada mutasi arus kas pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>

