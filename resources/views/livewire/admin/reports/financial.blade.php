<div class="space-y-6">
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
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Sumber Arus Kas</label>
                <select wire:model.live="source" class="form-input mt-3">
                    <option value="all">Semua</option>
                    <option value="payment">Pemasukan Saja</option>
                    <option value="expense">Pengeluaran Saja</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Metode</label>
                <select wire:model.live="method" class="form-input mt-3">
                    <option value="all">Semua</option>
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
            Periode laporan:
            {{ \Carbon\Carbon::parse($range['start_date'])->format('d/m/Y') }}
            s/d
            {{ \Carbon\Carbon::parse($range['end_date'])->format('d/m/Y') }}.
        </p>
    </div>

    @php
        $cf = $cashflow_summary;
        $pl = $profit_loss;
        $bs = $balance_sheet;
    @endphp

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Saldo Awal</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $cf['opening_balance'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/20">
            <div class="text-sm text-emerald-700 dark:text-emerald-300">Kas Masuk</div>
            <div class="text-xl font-semibold text-emerald-700 dark:text-emerald-300">Rp {{ number_format((float) $cf['cash_in'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-900/40 dark:bg-rose-900/20">
            <div class="text-sm text-rose-700 dark:text-rose-300">Kas Keluar</div>
            <div class="text-xl font-semibold text-rose-700 dark:text-rose-300">Rp {{ number_format((float) $cf['cash_out'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Laba Kotor</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $pl['gross_profit'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Laba Bersih</div>
            <div class="text-xl font-semibold {{ $pl['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                Rp {{ number_format((float) $pl['net_profit'], 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <x-card title="Laba Rugi Sederhana" description="Ringkasan pendapatan dan beban pada periode terpilih.">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Pendapatan</td>
                            <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format((float) $pl['revenue'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">HPP</td>
                            <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format((float) $pl['cogs'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Laba Kotor</td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $pl['gross_profit'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Beban Operasional</td>
                            <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format((float) $pl['operating_expense'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-semibold text-gray-900 dark:text-white">Laba Bersih</td>
                            <td class="py-2 text-right font-semibold {{ $pl['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                Rp {{ number_format((float) $pl['net_profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Neraca Sederhana" description="Posisi aset dibanding kewajiban dan modal.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Aset</p>
                    <div class="mt-2 space-y-1">
                        @foreach ($bs['assets'] as $asset)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-200">{{ $asset['label'] }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format((float) $asset['value'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 border-t border-gray-200 pt-2 text-sm font-semibold dark:border-gray-800">
                        <span class="text-gray-700 dark:text-gray-200">Total Aset</span>
                        <span class="float-right text-gray-900 dark:text-white">Rp {{ number_format((float) $bs['asset_total'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Kewajiban + Modal</p>
                    <div class="mt-2 space-y-1">
                        @foreach ($bs['liabilities'] as $liability)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-200">{{ $liability['label'] }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format((float) $liability['value'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-200">Modal</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format((float) $bs['equity'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-200">Laba Berjalan</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format((float) $bs['current_profit'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="mt-3 border-t border-gray-200 pt-2 text-sm font-semibold dark:border-gray-800">
                        <span class="text-gray-700 dark:text-gray-200">Total Kewajiban + Modal</span>
                        <span class="float-right text-gray-900 dark:text-white">Rp {{ number_format((float) $bs['total_liability_and_equity'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <p class="mt-3 text-xs {{ abs((float) $bs['delta']) <= 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                Selisih neraca: Rp {{ number_format((float) $bs['delta'], 0, ',', '.') }}
            </p>
        </x-card>
    </div>

    <x-card title="Mutasi Arus Kas" description="Detail pemasukan dan pengeluaran dengan saldo berjalan.">
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
                            Rp {{ number_format((float) $cf['opening_balance'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @forelse ($cashflow_rows as $row)
                        <tr>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['source'] === 'payment' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $row['reference'] }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['description'] }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 uppercase">{{ $row['method'] }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-300 whitespace-nowrap">
                                {{ (float) $row['cash_in'] > 0 ? 'Rp ' . number_format((float) $row['cash_in'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right text-rose-700 dark:text-rose-300 whitespace-nowrap">
                                {{ (float) $row['cash_out'] > 0 ? 'Rp ' . number_format((float) $row['cash_out'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white whitespace-nowrap">Rp {{ number_format((float) $row['balance'], 0, ',', '.') }}</td>
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

