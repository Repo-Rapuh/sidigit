@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    [
                        'title' => 'Total Users',
                        'value' => '1,234',
                        'change' => '+4.5%',
                        'change_color' => 'text-success-600',
                        'icon' => 'users',
                    ],
                    [
                        'title' => 'Active Orders',
                        'value' => '87',
                        'change' => '+2.1%',
                        'change_color' => 'text-success-600',
                        'icon' => 'orders',
                    ],
                    [
                        'title' => 'Products Listed',
                        'value' => '642',
                        'change' => '−0.8%',
                        'change_color' => 'text-error-500',
                        'icon' => 'products',
                    ],
                    [
                        'title' => 'Audit Events',
                        'value' => '1,028',
                        'change' => '+12.4%',
                        'change_color' => 'text-success-600',
                        'icon' => 'audit',
                    ],
                ];
            @endphp

            @foreach ($cards as $card)
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm transition hover:shadow-theme-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['title'] }}</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $card['value'] }}</p>
                        </div>
                        <span
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                            @if ($card['icon'] === 'users')
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M12.5 4.5a3.5 3.5 0 1 1-7 0a3.5 3.5 0 0 1 7 0Z" stroke="currentColor"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M20 21v-2a3.5 3.5 0 0 0-2.5-3.34" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M17 4a3 3 0 0 1 0 6" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @elseif ($card['icon'] === 'orders')
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7 3h10a2 2 0 0 1 2 2v15.382a.5.5 0 0 1-.757.429L12 18l-6.243 2.811A.5.5 0 0 1 5 20.382V5a2 2 0 0 1 2-2Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M9 8h6M9 12h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            @elseif ($card['icon'] === 'products')
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 3L3 7.5L12 12L21 7.5L12 3Z" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M3 16.5L12 21L21 16.5" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M3 12L12 16.5L21 12" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @else
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 8v4l3 1.5M21 12a9 9 0 1 1-18 0a9 9 0 0 1 18 0Z" stroke="currentColor"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                        </span>
                    </div>
                    <p class="mt-3 text-xs font-medium {{ $card['change_color'] }}">
                        {{ $card['change'] }} vs last month
                    </p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Latest five orders requiring attention</p>
                    </div>
                    <a href="{{ Route::has('orders.index') ? route('orders.index') : '#' }}"
                        class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">View all</a>
                </div>
                <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Order
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Customer
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-900 dark:bg-gray-950/50">
                            @foreach (range(1, 5) as $index)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                        #INV-{{ str_pad((string) $index, 3, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        Jane Cooper
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                            Paid
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                        $1,240.00
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Links</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Jump straight into frequent admin tasks.</p>
                <div class="mt-5 space-y-3">
                    <a href="{{ Route::has('products.create') ? route('products.create') : '#' }}"
                        class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        <span>Add a new product</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 10h10M10 5v10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </a>

                    <a href="{{ Route::has('users.create') ? route('users.create') : '#' }}"
                        class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        <span>Invite a team member</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 4a3 3 0 1 1-3 3a3 3 0 0 1 3-3ZM6 17a4 4 0 1 1 8 0v1H6v-1Z" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>

                    <a href="{{ Route::has('audit-logs.index') ? route('audit-logs.index') : '#' }}"
                        class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        <span>Review audit trail</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 6v4l2.5 1.5M17 10a7 7 0 1 1-14 0a7 7 0 0 1 14 0Z" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
