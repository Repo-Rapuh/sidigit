<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/60">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Periode</label>
                <select wire:model.live="period" class="form-input mt-3">
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="custom">Custom Range</option>
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
            Menampilkan data jurnal periode {{ \Carbon\Carbon::parse($range['start_date'] ?? $start_date)->format('d/m/Y') }}
            s/d
            {{ \Carbon\Carbon::parse($range['end_date'] ?? $end_date)->format('d/m/Y') }}.
        </p>
    </div>

    <x-card :title="$title" :description="$description">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format((float) $card['value'], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Akun {{ $card['code'] }}</p>
                </div>
            @endforeach
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <x-card title="Ringkasan Kelompok Akun" description="Posisi saldo berdasarkan tipe akun.">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Asset</td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $typeSummary['asset'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Liability</td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $typeSummary['liability'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Equity</td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $typeSummary['equity'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Revenue</td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $typeSummary['revenue'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Expense</td>
                            <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $typeSummary['expense'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white font-semibold">Estimasi Laba (Revenue - Expense)</td>
                            <td class="py-2 text-right font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float) $typeSummary['estimated_profit'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Akun Kunci" description="Akun yang paling sering dipakai di operasional harian.">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="py-2">Akun</th>
                            <th class="py-2 text-right">Debit</th>
                            <th class="py-2 text-right">Kredit</th>
                            <th class="py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($accountHighlights as $row)
                            <tr>
                                <td class="py-2 text-gray-700 dark:text-gray-200">
                                    {{ $row['label'] }}<br>
                                    <span class="text-xs text-gray-500">({{ $row['code'] }})</span>
                                </td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-200">Rp {{ number_format((float) $row['debit'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-200">Rp {{ number_format((float) $row['credit'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $row['balance'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <x-card title="Jurnal Terbaru" description="10 jurnal terakhir sesuai periode filter.">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-2">No Jurnal</th>
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Deskripsi</th>
                        <th class="px-3 py-2 text-right">Total Debit</th>
                        <th class="px-3 py-2 text-right">Total Kredit</th>
                        <th class="px-3 py-2">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($recentJournals as $journal)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $journal->journal_no }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $journal->journal_date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $journal->description ?: '-' }}</td>
                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200 whitespace-nowrap">Rp {{ number_format((float) $journal->total_debit, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200 whitespace-nowrap">Rp {{ number_format((float) $journal->total_credit, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $journal->postedBy?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                Belum ada jurnal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
