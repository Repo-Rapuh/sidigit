@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $filters = $summary['filters'] ?? [];
        $kpis = $summary['kpis'] ?? [];
        $orderPipeline = $summary['order_pipeline'] ?? collect();
        $actionItems = $summary['action_items'] ?? collect();
        $productionSnapshot = $summary['production_snapshot'] ?? collect();
        $urgentProductionJobs = $summary['urgent_production_jobs'] ?? collect();
        $cashflow = $summary['cashflow'] ?? [];
        $topReceivables = $summary['top_receivables'] ?? collect();
        $lowStockMaterials = $summary['low_stock_materials'] ?? collect();
        $recentActivities = $summary['recent_activities'] ?? collect();

        $fmtRp = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $fmtNum = fn($value) => number_format((float) $value, 0, ',', '.');
        $fmtDate = fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y') : '-';

        $toneClasses = [
            'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-200',
            'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
            'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
            'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200',
            'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200',
            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200',
            'violet' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-200',
            'green' => 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-200',
            'teal' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-200',
            'red' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200',
        ];

        $orderStatusLabel = function (string $status) {
            return [
                'draft' => 'Draft',
                'quotation' => 'Quotation',
                'approval' => 'Approval',
                'pembayaran' => 'Pembayaran',
                'desain' => 'Desain',
                'produksi' => 'Produksi',
                'qc' => 'QC',
                'siap' => 'Siap Diambil',
                'diambil' => 'Diambil',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
            ][$status] ?? ucfirst($status);
        };

        $orderStatusTone = function (string $status) {
            return match ($status) {
                'draft' => 'slate',
                'quotation' => 'amber',
                'approval' => 'emerald',
                'pembayaran' => 'sky',
                'desain' => 'indigo',
                'produksi' => 'blue',
                'qc' => 'violet',
                'siap', 'diambil', 'selesai' => 'green',
                'dibatalkan' => 'red',
                default => 'slate',
            };
        };
    @endphp

    <div class="space-y-6">
        <section
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-500 dark:text-brand-400">Dashboard
                        Operasional</p>
                    <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Ringkasan Aktivitas Harian</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Periode {{ $filters['label'] ?? '-' }}
                        @if (!empty($activeBranchName))
                            · Cabang {{ $activeBranchName }}
                        @else
                            · Semua cabang
                        @endif
                    </p>
                </div>

                <form method="GET" action="{{ route('dashboard') }}"
                    class="grid w-full max-w-3xl grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-4" x-data="{ range: '{{ $filters['range'] ?? 'today' }}' }">
                    <select name="range" x-model="range"
                        class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="today">Hari Ini</option>
                        <option value="7d">7 Hari</option>
                        <option value="month">Bulan Ini</option>
                        <option value="custom">Custom</option>
                    </select>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" x-bind:disabled="range !== 'custom'"
                        class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" x-bind:disabled="range !== 'custom'"
                        class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                    <button type="submit"
                        class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                        Terapkan
                    </button>
                </form>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-6">
                <a href="{{ route('orders.create') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                    + Tambah Order
                </a>
                <a href="{{ route('orders.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                    Daftar Order
                </a>
                <a href="{{ route('productions.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                    Board Produksi
                </a>
                <a href="{{ route('stocks.reservations') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                    Reservasi Stok
                </a>
                <a href="{{ route('reports.sales') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                    Laporan Sales
                </a>
                <a href="{{ route('accounting.overview') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                    Akuntansi
                </a>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Order Hari Ini</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $fmtNum($kpis['orders_today'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Omzet Hari Ini</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $fmtRp($kpis['revenue_today'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Piutang Aktif</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $fmtRp($kpis['receivables_active'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Job Produksi Aktif</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $fmtNum($kpis['production_active'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Order Overdue</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $fmtNum($kpis['overdue_orders'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Bahan Menipis</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $fmtNum($kpis['low_stock_count'] ?? 0) }}</p>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pipeline Order</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Distribusi order berdasarkan status pada periode aktif.</p>
                    </div>
                    <a href="{{ route('orders.index') }}"
                        class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Lihat order</a>
                </div>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6">
                    @foreach ($orderPipeline as $item)
                        @php
                            $badge = $toneClasses[$item['tone']] ?? $toneClasses['slate'];
                        @endphp
                        <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $badge }}">
                                {{ $item['label'] }}
                            </span>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $fmtNum($item['count']) }}</p>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Arus Kas Ringkas</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pemasukan vs pengeluaran pada periode aktif.</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-800">
                        <dt class="text-gray-500 dark:text-gray-400">Kas Masuk</dt>
                        <dd class="font-semibold text-emerald-600 dark:text-emerald-300">{{ $fmtRp($cashflow['income'] ?? 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-800">
                        <dt class="text-gray-500 dark:text-gray-400">Pengeluaran</dt>
                        <dd class="font-semibold text-red-600 dark:text-red-300">{{ $fmtRp($cashflow['expense'] ?? 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-800">
                        <dt class="text-gray-500 dark:text-gray-400">Net Cashflow</dt>
                        @php $net = (float) ($cashflow['net'] ?? 0); @endphp
                        <dd class="font-semibold {{ $net >= 0 ? 'text-brand-600 dark:text-brand-300' : 'text-red-600 dark:text-red-300' }}">
                            {{ $fmtRp($net) }}
                        </dd>
                    </div>
                </dl>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Perlu Tindakan Sekarang</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Order yang perlu follow-up langsung dari tim.</p>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2">Order</th>
                                <th class="px-3 py-2">Customer</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Deadline</th>
                                <th class="px-3 py-2 text-right">Sisa Tagihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950/20">
                            @forelse ($actionItems as $item)
                                @php
                                    $tone = $orderStatusTone($item['status']);
                                @endphp
                                <tr>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('orders.edit', ['order' => $item['id']]) }}"
                                            class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-300">
                                            {{ $item['order_no'] }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $item['customer_name'] }}</td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $toneClasses[$tone] ?? $toneClasses['slate'] }}">
                                            {{ $orderStatusLabel($item['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                        <span class="{{ $item['is_overdue'] ? 'font-semibold text-red-600 dark:text-red-300' : '' }}">
                                            {{ $fmtDate($item['deadline']) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white">
                                        {{ $fmtRp($item['remaining']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Tidak ada order yang perlu tindakan segera.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Snapshot Produksi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah job per kolom kanban saat ini.</p>
                <div class="mt-4 space-y-2">
                    @foreach ($productionSnapshot as $item)
                        @php
                            $badge = $toneClasses[$item['tone']] ?? $toneClasses['slate'];
                        @endphp
                        <div class="flex items-center justify-between rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-800">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $item['label'] }}</span>
                            <strong class="text-gray-900 dark:text-white">{{ $fmtNum($item['count']) }}</strong>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('productions.index') }}"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-brand-500 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                    Buka Board Produksi
                </a>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Piutang</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Order dengan sisa tagihan terbesar.</p>
                <div class="mt-4 space-y-2">
                    @forelse ($topReceivables as $item)
                        <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('orders.edit', ['order' => $item['id']]) }}"
                                    class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-300">
                                    {{ $item['order_no'] }}
                                </a>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $fmtRp($item['remaining']) }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['customer_name'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada piutang aktif.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Stok Menipis</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Material di bawah atau sama dengan reorder level.</p>
                <div class="mt-4 space-y-2">
                    @forelse ($lowStockMaterials as $item)
                        <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Saldo: {{ $fmtNum($item['stock_balance']) }} {{ $item['unit_name'] }} ·
                                ROP: {{ $fmtNum($item['reorder_level']) }} {{ $item['unit_name'] }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada material yang melewati batas reorder.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Order, pembayaran, dan produksi terbaru.</p>
                <div class="mt-4 space-y-3">
                    @forelse ($recentActivities as $item)
                        <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</p>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ optional($item['timestamp'])->format('d/m H:i') }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">{{ $item['description'] }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['meta'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada aktivitas terbaru.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Job Produksi Urgent</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Task produksi yang perlu diprioritaskan.</p>
                </div>
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">Order</th>
                            <th class="px-3 py-2">Produk</th>
                            <th class="px-3 py-2">Tahap</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Deadline</th>
                            <th class="px-3 py-2">PIC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950/20">
                        @forelse ($urgentProductionJobs as $job)
                            @php
                                $tone = match ($job['status']) {
                                    'antrian' => 'slate',
                                    'in_progress' => 'blue',
                                    'qc' => 'violet',
                                    'siap_diambil' => 'green',
                                    default => 'slate',
                                };
                                $stageLabel = $job['stage'] === 'desain' ? 'Desain' : 'Produksi';
                                $statusLabel = [
                                    'antrian' => 'Antrian',
                                    'in_progress' => 'Produksi',
                                    'qc' => 'QC',
                                    'siap_diambil' => 'Siap Diambil',
                                ][$job['status']] ?? ucfirst($job['status']);
                            @endphp
                            <tr>
                                <td class="px-3 py-2 font-semibold text-brand-600 dark:text-brand-300">{{ $job['order_no'] }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $job['product_name'] }} ({{ $fmtNum($job['qty']) }})</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $stageLabel }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $toneClasses[$tone] ?? $toneClasses['slate'] }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $fmtDate($job['deadline']) }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $job['claimed_by'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Belum ada job produksi aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
