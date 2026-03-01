@php
    $status = $row->status ?? 'draft';
    $label = [
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
    ][$status] ?? ucfirst((string) $status);

    $badgeClass = match ($status) {
        'quotation' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
        'approval' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
        'produksi' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200',
        'selesai' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-200',
        'dibatalkan' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-200',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
    };
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
    {{ $label }}
</span>
