<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_no }}</title>
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
        .card { border: 1px solid #e5e7eb; padding: 6px; border-radius: 6px; background: #f9fafb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px; border-bottom: 1px solid #e5e7eb; }
        th { text-align: left; color: #6b7280; font-weight: 600; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; border-bottom: 1px dotted #e5e7eb; }
        .meta-label { width: 32%; background: #f3f4f6; color: #374151; font-weight: 700; }
        .text-right { text-align: right; }
        .totals td { border-bottom: none; }
        .no-print { display: block; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 10px; }
        .action-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; padding: 8px 16px; font-size: 14px; font-weight: 600; text-decoration: none; border: 1px solid #e5e7eb; color: #374151; background: #fff; box-shadow: 0 1px 2px rgba(17, 24, 39, 0.06); }
        .btn-primary { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .page { max-width: 210mm; width: 100%; margin: 0 auto; padding: 16px; }
        .paper { background: #fff; border-radius: 16px; box-shadow: 0 10px 24px rgba(17, 24, 39, 0.08); border: 1px solid #e5e7eb; padding: 16px; min-height: 148mm; }
        .payment-grid { display: flex; gap: 12px; align-items: stretch; justify-content: space-between; }
        .payment-box { border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; padding: 8px; }
        .totals-box { border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; padding: 0; overflow: hidden; }
        .totals-box .meta-table { height: 100%; }
        .totals-box .meta-table tr { height: 20%; }
        .payment-list { margin: 6px 0 0; padding: 0; list-style: none; }
        .payment-list li { margin-bottom: 4px; }
        .qr-box { width: 90px; height: 90px; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; color: #6b7280; font-weight: 700; font-size: 10px; background: #fff; border-radius: 6px; }
        .qr-box img { width: 100%; height: 100%; object-fit: contain; display: block; }
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
        $payments = $order->payments?->sortBy('paid_at') ?? collect();
        $paymentsAsc = $payments->values();
        $statusHistory = $order->statusLogs ?? collect();
        $paidAmount = (float) ($order->paid_amount ?? 0);
        $grandTotal = (float) ($order->grand_total ?? 0);
        $balance = max(0, $grandTotal - $paidAmount);
        $change = max(0, $paidAmount - $grandTotal);
        $runningBalance = $grandTotal;
        $paymentLedgerRows = collect([
            [
                'date' => $order->order_date,
                'description' => 'Tagihan Order ' . $order->order_no,
                'debit' => $grandTotal,
                'credit' => 0.0,
                'balance' => max(0, $runningBalance),
            ],
        ]);
        foreach ($paymentsAsc as $payment) {
            $amount = (float) ($payment->amount ?? 0);
            $balanceBefore = $runningBalance;
            $runningBalance -= $amount;

            $changeFromPayment = max(0, $amount - max(0, $balanceBefore));
            $description = ucfirst((string) ($payment->method ?? '-'));
            if (!empty($payment->notes)) {
                $description .= ' - ' . $payment->notes;
            }
            if ($changeFromPayment > 0) {
                $description .= ' (Kembalian Rp ' . number_format($changeFromPayment, 0, ',', '.') . ')';
            }

            $paymentLedgerRows->push([
                'date' => $payment->paid_at,
                'description' => $description,
                'debit' => 0.0,
                'credit' => $amount,
                'balance' => max(0, $runningBalance),
            ]);
        }
        $branch = $order->branch;
        $qrisUrl = null;
        if (!empty($branch?->qris_path)) {
            $disk = \App\Support\UploadStorage::disk();
            try {
                $storage = Storage::disk($disk);
                $driver = config("filesystems.disks.{$disk}.driver");
                $qrisUrl = $driver === 's3'
                    ? $storage->temporaryUrl($branch->qris_path, now()->addMinutes(10))
                    : $storage->url($branch->qris_path);
            } catch (\Throwable $e) {
                $qrisUrl = null;
            }
        }
        $statusLabel = $statusLabels[$status] ?? ucfirst($status);

        $bankAccounts = $bankAccounts ?? collect();
    @endphp

    <div class="page">
        @if (empty($print))
            <div class="action-bar no-print">
                <a href="{{ route('orders.index') }}" class="btn">Kembali ke Order</a>
                <div class="action-group">
                    <a href="{{ route('orders.invoice', ['order' => $order->id, 'print' => 1]) }}" target="_blank" class="btn">
                        Print Invoice
                    </a>
                    <a href="{{ route('orders.invoice.pdf', $order->id) }}" target="_blank" class="btn btn-primary">
                        Download PDF
                    </a>
                </div>
            </div>
        @endif

        <div class="paper">
            <table class="row">
                <tr>
                    <td class="col" style="width: 55%;">
                        <h1>{{ $companyName }}</h1>
                        <p class="text-muted">Invoice untuk order percetakan digital</p>
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
                                <td class="meta-label">No Invoice</td>
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

            <div class="section payment-grid">
                <div class="payment-box" style="width: 55%;">
                    <h2>Rekening Pembayaran</h2>
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div style="flex: 1;">
                            <ul class="payment-list">
                                @forelse ($bankAccounts as $account)
                                    <li>
                                        <strong>{{ $account->bank_name }}</strong>
                                        &bull; {{ $account->rekening_number }}
                                        &bull; a.n. {{ $account->account_name }}
                                    </li>
                                @empty
                                    <li class="text-muted">Belum ada rekening bank.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="qr-box">
                            @if ($qrisUrl)
                                <img src="{{ $qrisUrl }}" alt="QRIS"
                                    onerror="this.onerror=null;this.parentElement.textContent='QRIS';">
                            @else
                                QRIS
                            @endif
                        </div>
                    </div>
                </div>

                <div style="width: 45%;" class="totals-box">
                    <table class="meta-table">
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
                        <tr>
                            <td class="meta-label">Terbayar</td>
                            <td class="text-right"><strong>Rp {{ number_format($paidAmount, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Sisa Tagihan</td>
                            <td class="text-right"><strong>Rp {{ number_format($balance, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Kembalian</td>
                            <td class="text-right"><strong>Rp {{ number_format($change, 0, ',', '.') }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="section">
                <p class="text-muted"><strong>Catatan:</strong> {{ $order->notes ?: 'Tidak ada catatan.' }}</p>
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

            <div class="section">
                <h2>Riwayat Pembayaran</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Kredit</th>
                            <th class="text-right">Sisa Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paymentLedgerRows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['date'] ? \Illuminate\Support\Carbon::parse($row['date'])->format('d M Y H:i') : '-' }}</td>
                                <td>{{ $row['description'] }}</td>
                                <td class="text-right">
                                    @if ($row['debit'] > 0)
                                        <strong>Rp {{ number_format((float) $row['debit'], 0, ',', '.') }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($row['credit'] > 0)
                                        <strong>Rp {{ number_format((float) $row['credit'], 0, ',', '.') }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right"><strong>Rp {{ number_format((float) $row['balance'], 0, ',', '.') }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Belum ada transaksi pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td class="text-right"><strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
                            <td class="text-right"><strong>Rp {{ number_format($paidAmount, 0, ',', '.') }}</strong></td>
                            <td class="text-right"><strong>Rp {{ number_format($balance, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                @if ($change > 0)
                    <p class="text-muted" style="margin-top: 8px;"><strong>Kembalian:</strong> Rp {{ number_format($change, 0, ',', '.') }}</p>
                @endif
            </div>
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
