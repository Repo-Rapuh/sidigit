<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Quotation {{ $order->order_no }}</title>
    <style>
        @page { size: A5 landscape; margin: 6mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #111827; margin: 0; background: #f3f4f6; }
        h1 { font-size: 14px; margin: 0; }
        h2 { font-size: 11px; margin: 0 0 3px; }
        .text-muted { color: #6b7280; }
        .section { margin-top: 10px; }
        .row { width: 100%; }
        .col { vertical-align: top; }
        .card { border: 1px solid ; padding: 6px; border-radius: 6px; background: #f9fafb; }
        table { width: 100%; border-collapse: collapse; }
        th, td, tr { padding: 4px; border: 1px solid; }
        th { text-align: left; color: #6b7280; font-weight: 600; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; border-bottom: 1px dotted #e5e7eb; }
        .meta-label { width: 32%; background: #f3f4f6; color: #374151; font-weight: 700; }
        .no-outer-border,
        .no-outer-border > tbody > tr,
        .no-outer-border > tbody > tr > td,
        .no-outer-border > tr,
        .no-outer-border > tr > td {
            border: 0 !important;
        }
        .text-right { text-align: right; }
        .totals td { border-bottom: none; }
        .no-print { display: block; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 10px; }
        .action-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; padding: 8px 16px; font-size: 14px; font-weight: 600; text-decoration: none; border: 1px solid #e5e7eb; color: #374151; background: #fff; box-shadow: 0 1px 2px rgba(17, 24, 39, 0.06); }
        .btn-primary { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .page { max-width: 210mm; width: 100%; margin: 0 auto; padding: 16px; }
        .paper { background: #fff; border-radius: 16px; box-shadow: 0 10px 24px rgba(17, 24, 39, 0.08); border: 1px solid #e5e7eb; padding: 16px; min-height: 148mm; }
        @media (min-width: 768px) {
            .page { padding: 32px; }
            .paper { padding: 20px 24px; }
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .page { margin: 0; padding: 0; }
            .paper { box-shadow: none; border: 0; padding: 0; border-radius: 0; min-height: auto; }
        }
    </style>
</head>

<body>
    @php
        $companyName = config('app.name', 'Sidigit');
        $companyEmail = config('mail.from.address', '-');
        $companyPhone = config('app.company_phone', '-');
        $companyAddress = config('app.company_address', 'Alamat belum diatur.');
        $customer = $order->customer;
        $issuedAt = $order->order_date?->format('d M Y') ?? now()->format('d M Y');
        $dueAt = $order->deadline?->format('d M Y') ?? '-';
        $status = $order->status ?? 'draft';
        $statusLabels = [
            'draft' => 'Draft',
            'quotation' => 'Quotation',
            'approval' => 'Approved',
            'pembayaran' => 'Pembayaran',
            'desain' => 'Desain',
            'produksi' => 'Produksi',
            'qc' => 'QC',
            'siap' => 'Siap Diambil/Dikirim',
            'diambil' => 'Diambil',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];
        $statusLabel = $statusLabels[$status] ?? ucfirst($status);
        $statusHistory = $order->statusLogs ?? collect();
    @endphp

    <div class="page">
        @if (empty($print))
            <div class="action-bar no-print">
                <a href="{{ route('orders.index') }}" class="btn">Kembali ke Order</a>
                <div class="action-group">
                    <a href="{{ route('orders.quotation', ['order' => $order->id, 'print' => 1]) }}" target="_blank" class="btn">
                        Print Quotation
                    </a>
                    <a href="{{ route('orders.quotation.pdf', $order->id) }}" target="_blank" class="btn btn-primary">
                        Download PDF
                    </a>
                </div>
            </div>
        @endif

        <div class="paper">
            <table class="row no-outer-border">
                <tr>
                    <td class="col" style="width: 55%;">
                        <h1>{{ $companyName }}</h1>
                        <p class="text-muted">Quotation untuk order percetakan digital</p>
                        <p class="text-muted">{{ $companyAddress }}</p>
                        <p class="text-muted">{{ $companyPhone }} · {{ $companyEmail }}</p>
                        <p class="text-muted">{{ $issuedAt }}</p>
                    </td>
                    <td class="col" style="width: 45%;">
                        <table class="meta-table" style="border: 1px solid #e5e7eb;">
                            <tr>
                                <td class="meta-label">Kepada Yth.</td>
                                <td><strong>{{ $customer?->name ?? 'Umum' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Telp</td>
                                <td>{{ $customer?->phone_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Alamat</td>
                                <td>{{ $customer?->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">No Quotation</td>
                                <td><strong>{{ $order->order_no }}</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="section">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Bahan</th>
                            <th>Ukuran</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Diskon</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            @php
                                $size = $item->length_cm && $item->width_cm
                                    ? number_format((float) $item->length_cm, 2, ',', '.') . ' x ' . number_format((float) $item->width_cm, 2, ',', '.') . ' cm'
                                    : '-';
                                $finishNames = $item->finishes
                                    ->map(fn ($finish) => $finish->finish?->name)
                                    ->filter()
                                    ->implode(', ');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $item->product?->name ?? '-' }}</strong><br>
                                    @if (!empty($finishNames))
                                        <span class="text-muted">Finishing: {{ $finishNames }}</span>
                                    @endif
                                </td>
                                <td>{{ $item->material?->name ?? '-' }}</td>
                                <td>{{ $size }}</td>
                                <td class="text-right">{{ number_format((float) $item->qty, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format((float) $item->discount, 0, ',', '.') }}</td>
                                <td class="text-right"><strong>Rp {{ number_format((float) $item->total, 0, ',', '.') }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="section">
                <table class="meta-table" style="width: 45%; margin-left: auto; border: 1px solid #e5e7eb;">
                    <tr>
                        <td class="meta-label">Total Harga</td>
                        <td class="text-right"><strong>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Diskon</td>
                        <td class="text-right"><strong>Rp {{ number_format((float) $order->total_discount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Grand Total</td>
                        <td class="text-right"><strong>Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>

            @if (empty($print))
                <div class="section no-print">
                    <h2>Riwayat Status</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($statusHistory as $log)
                                @php
                                    $logStatus = $statusLabels[$log->status] ?? ucfirst((string) $log->status);
                                    $logUser = $log->changedByUser?->name
                                        ? $log->changedByUser->name . ' (#' . ($log->changed_by ?? '-') . ')'
                                        : 'User #' . ($log->changed_by ?? '-');
                                @endphp
                                <tr>
                                    <td>{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ $logStatus }}</td>
                                    <td>{{ $logUser }}</td>
                                    <td>{{ $log->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">Belum ada riwayat status.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if (!empty($print))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => window.print(), 300);
            });
        </script>
    @endif
</body>
