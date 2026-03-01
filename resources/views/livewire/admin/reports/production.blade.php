<div class="space-y-6">
    <x-breadcrumbs :items="$breadcrumbs" />

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/60">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-forms.input label="Dari" name="start_date" type="date" wire:model.live="start_date" />
            <x-forms.input label="Sampai" name="end_date" type="date" wire:model.live="end_date" />
        </div>
    </div>

    @php $s = $summary; @endphp
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Job Masuk</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($s['incoming_jobs'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Job Selesai</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($s['completed_jobs'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">WIP (Antrian+Proses+QC)</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($s['wip_jobs'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Lead Time Rata-rata</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format((float) $s['avg_lead_hours'], 2, ',', '.') }} jam</div>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/20">
            <div class="text-sm text-emerald-700 dark:text-emerald-300">QC Pass</div>
            <div class="text-2xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($s['qc_pass'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-900/40 dark:bg-rose-900/20">
            <div class="text-sm text-rose-700 dark:text-rose-300">QC Fail</div>
            <div class="text-2xl font-semibold text-rose-700 dark:text-rose-300">{{ number_format($s['qc_fail'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">On Time</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($s['on_time'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Terlambat</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($s['late'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <x-card title="Distribusi Status Saat Ini" description="Jumlah task berdasarkan kolom board produksi saat ini.">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2">Status</th>
                            <th class="py-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($status_counts as $row)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-gray-100">{{ $row['label'] }}</td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format((int) $row['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Beban Kerja per Role (WIP)" description="Task aktif yang masih berjalan, dikelompokkan per role.">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2">Role</th>
                            <th class="py-2 text-right">Jumlah Task</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($role_workloads as $row)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-gray-100">{{ $row->role_name }}</td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format((int) $row->total_jobs, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-4 text-center text-gray-500">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <x-card title="Top Produk Produksi" description="10 produk dengan jumlah job terbanyak pada periode ini.">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr>
                        <th class="py-2">Produk</th>
                        <th class="py-2 text-right">Total Job</th>
                        <th class="py-2 text-right">Total Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($top_products as $row)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-gray-100">{{ $row->product_name }}</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format((int) $row->total_jobs, 0, ',', '.') }}</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format((float) $row->total_qty, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>

