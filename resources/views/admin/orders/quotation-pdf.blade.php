<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Quotation {{ $order->order_no }}</title>
    <style>
        @page { size: A5 landscape; margin: 6mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #111827; }
        h1 { font-size: 14px; margin: 0; }
        h2 { font-size: 11px; margin: 0 0 3px; }
        .text-muted { color: #6b7280; }
        .section { margin-top: 10px; }
        .row { width: 100%; }
        .col { vertical-align: top; }
        .card { border: 1px solid #e5e7eb; padding: 6px; border-radius: 6px; background: #f9fafb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px; border-bottom: 1px solid #e5e7eb; }
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
        $statusLabel = [
            'draft' => 'Draft',
            'quotation' => 'Quotation',
            'approval' => 'Approved',
            'pembayaran' => 'Pembayaran',
            'desain' => 'Desain',
            'produksi' => 'Produksi',
            'qc' => 'QC',
            'siap' => 'Siap Diambil/Dikirim',
            'selesai' => 'Selesai',
        ][$status] ?? ucfirst($status);
    @endphp

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
</body>

</html>
