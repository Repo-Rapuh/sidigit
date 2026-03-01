@php
    $labels = \App\Models\ProductionJob::statusOptions();
    $status = (string) ($row->status ?? 'antrian');
    $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));

    $classes = [
        'antrian' => 'bg-gray-100 text-gray-700 dark:bg-gray-700/40 dark:text-gray-200',
        'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200',
        'qc' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
        'siap_diambil' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-200',
    ];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes[$status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700/40 dark:text-gray-200' }}">
    {{ $label }}
</span>
