<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->order_no }}</title>
    <style>
        :root {
            color-scheme: light;
            --paper-width: {{ (int) ($paperWidth ?? 80) }}mm;
            --page-max: {{ (int) (($paperWidth ?? 80) === 58 ? 360 : 420) }}px;
            --font-base: {{ (int) (($paperWidth ?? 80) === 58 ? 11 : 12) }}px;
            --paper-padding-y: {{ (int) (($paperWidth ?? 80) === 58 ? 8 : 10) }}px;
            --paper-padding-x: {{ (int) (($paperWidth ?? 80) === 58 ? 7 : 9) }}px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            font-family: "Courier New", Courier, monospace;
            color: #111827;
            font-size: var(--font-base);
            line-height: 1.35;
        }

        .page {
            max-width: var(--page-max);
            margin: 20px auto;
            padding: 0 12px;
        }

        .paper {
            width: var(--paper-width);
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(17, 24, 39, .08);
            padding: var(--paper-padding-y) var(--paper-padding-x);
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .divider {
            margin: 8px 0;
            border: 0;
            border-top: 1px dashed #9ca3af;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .items td:first-child {
            width: 68%;
            padding-right: 6px;
        }

        .items td:last-child {
            width: 32%;
            text-align: right;
            white-space: nowrap;
        }

        .summary td:first-child {
            width: 58%;
        }

        .summary td:last-child {
            width: 42%;
            text-align: right;
            white-space: nowrap;
        }

        .title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .order-no {
            font-size: 14px;
            font-weight: 700;
        }

        .actions {
            margin: 10px 0 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            color: #374151;
            background: #fff;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
        }

        .chip {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 11px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }

        @page {
            size: var(--paper-width) auto;
            margin: 3mm;
        }

        @media print {
            body {
                margin: 0;
                background: #fff;
            }

            .page {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .paper {
                width: 100%;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    @php
        $companyName = $order->branch?->name ?: config('app.name', 'Sidigit');
        $companyAddress = $order->branch?->address ?: (config('app.company_address') ?: '-');
        $companyPhone = $order->branch?->phone_number ?: (config('app.company_phone') ?: '-');
        $companyEmail = $order->branch?->email ?: (config('mail.from.address') ?: '-');
        $paperWidth = in_array((int) ($paperWidth ?? 80), [58, 80], true) ? (int) $paperWidth : 80;
        $customer = $order->customer;
        $statusLabel = [
            'draft' => 'Draft',
            'quotation' => 'Quotation',
            'approval' => 'Approval',
            'pembayaran' => 'Pembayaran',
            'desain' => 'Desain',
            'produksi' => 'Produksi',
            'qc' => 'QC',
            'siap' => 'Siap',
            'diambil' => 'Diambil',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ][$order->status] ?? ucfirst((string) $order->status);
    @endphp

    <div class="page">
        <div class="no-print actions">
            <a href="{{ route('orders.index') }}" class="btn">Kembali</a>
            <a href="{{ route('orders.receipt', ['order' => $order->id, 'payment_id' => $selectedPayment->id, 'paper' => $paperWidth, 'print' => 1]) }}"
                target="_blank" class="btn btn-primary">Print Thermal</a>
            <a href="{{ route('orders.receipt', ['order' => $order->id, 'payment_id' => $selectedPayment->id, 'paper' => 58]) }}"
                class="btn {{ $paperWidth === 58 ? 'btn-primary' : '' }}">58mm</a>
            <a href="{{ route('orders.receipt', ['order' => $order->id, 'payment_id' => $selectedPayment->id, 'paper' => 80]) }}"
                class="btn {{ $paperWidth === 80 ? 'btn-primary' : '' }}">80mm</a>
        </div>

        <article class="paper">
            <header class="text-center">
                <div class="title">{{ $companyName }}</div>
                <div class="muted">{{ $companyAddress }}</div>
                <div class="muted">{{ $companyPhone }} · {{ $companyEmail }}</div>
            </header>

            <hr class="divider">

            <section>
                <div class="text-center order-no">{{ $order->order_no }}</div>
                <div class="text-center muted">{{ optional($selectedPayment->paid_at)->format('d/m/Y H:i') ?? '-' }}</div>
                <div class="text-center" style="margin-top: 3px;">
                    <span class="chip">{{ strtoupper((string) $selectedPayment->method) }}</span>
                    <span class="chip">{{ $statusLabel }}</span>
                </div>
            </section>

            <hr class="divider">

            <section>
                <table class="table">
                    <tr>
                        <td>Pelanggan</td>
                        <td class="text-right">{{ $customer?->name ?: 'Umum' }}</td>
                    </tr>
                    <tr>
                        <td>Telp</td>
                        <td class="text-right">{{ $customer?->phone_number ?: '-' }}</td>
                    </tr>
                </table>
            </section>

            <hr class="divider">

            <section>
                <div><strong>Ringkasan Item</strong></div>
                <table class="table items">
                    @foreach ($order->items as $item)
                        <tr>
                            <td>
                                {{ $item->product?->name ?: '-' }} x{{ number_format((float) $item->qty, 0, ',', '.') }}
                            </td>
                            <td>Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            </section>

            <hr class="divider">

            <section>
                <table class="table summary">
                    <tr>
                        <td>Total Tagihan</td>
                        <td>Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Bayar Sebelumnya</td>
                        <td>Rp {{ number_format($previousPaid, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Bayar Saat Ini</td>
                        <td>Rp {{ number_format($paymentAmount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Bayar</strong></td>
                        <td><strong>Rp {{ number_format($cumulativePaid, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Sisa Tagihan</td>
                        <td>Rp {{ number_format($remainingAfter, 0, ',', '.') }}</td>
                    </tr>
                    @if ($changeAtPayment > 0)
                        <tr>
                            <td>Kembalian Transaksi Ini</td>
                            <td>Rp {{ number_format($changeAtPayment, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if ($totalChange > 0)
                        <tr>
                            <td>Total Kembalian</td>
                            <td>Rp {{ number_format($totalChange, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </table>
            </section>

            @if (!empty($selectedPayment->notes))
                <hr class="divider">
                <section>
                    <div><strong>Catatan</strong></div>
                    <div>{{ $selectedPayment->notes }}</div>
                </section>
            @endif

            <hr class="divider">
            <footer class="text-center muted">
                Terima kasih atas kepercayaan Anda.
            </footer>
        </article>

        @if (($payments ?? collect())->count() > 1)
            <div class="no-print actions" style="margin-top: 10px;">
                @foreach ($payments as $payment)
                    <a href="{{ route('orders.receipt', ['order' => $order->id, 'payment_id' => $payment->id, 'paper' => $paperWidth]) }}"
                        class="btn">
                        {{ optional($payment->paid_at)->format('d/m H:i') ?? '-' }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if (!empty($print))
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    @endif
</body>

</html>
