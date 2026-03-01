<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tracking Order {{ $order->order_no }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-700 antialiased dark:bg-gray-950 dark:text-gray-200">
    <main class="mx-auto max-w-3xl p-4 md:p-8">
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 bg-gradient-to-r from-brand-500 to-indigo-500 px-6 py-6 text-white dark:border-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Tracking Order</p>
                <h1 class="mt-1 text-2xl font-semibold">{{ $order->order_no }}</h1>
                <p class="mt-2 text-sm text-white/85">Link ini bersifat publik. Simpan untuk memantau progres pesanan.</p>
            </div>

            <div class="grid gap-4 p-6 md:grid-cols-3">
                <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status Saat Ini</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $currentStatusLabel }}</p>
                </article>
                <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal Order</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $order->order_date?->format('d M Y') ?? '-' }}</p>
                </article>
                <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Deadline</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $order->deadline?->format('d M Y') ?? '-' }}</p>
                </article>
            </div>

            <div class="px-6 pb-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Riwayat Pengerjaan</h2>
                <ol class="mt-4 space-y-3">
                    @foreach ($timeline as $item)
                        <li class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/30">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['status_label'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ optional($item['created_at'])->format('d M Y H:i') ?: '-' }}
                                </p>
                            </div>
                            @if (!empty($item['note']))
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $item['note'] }}</p>
                            @endif
                            @if (!empty($item['changed_by']))
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Diubah oleh: {{ $item['changed_by'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    </main>
</body>

</html>

