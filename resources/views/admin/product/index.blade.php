@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp
    <div class="space-y-6">
        <div class="flex items-baseline justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Daftar Produk</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola daftar produk beserta komposisi material dan harga jualnya.
                </p>
            </div>
            @can('product.create')
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-900">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 4.167v11.666M4.167 10h11.666" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Tambah Produk
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div
                class="rounded-2xl border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm font-medium text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
                {{ session('error') }}
            </div>
        @endif

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-center font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                No.
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Produk
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Kategori
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Harga
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Material
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Deskripsi
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-center font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-900 dark:bg-gray-950/40">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-900/50" x-data="{ isConfirmOpen: false }">
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                                    {{ $loop->iteration + $products->firstItem() - 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold uppercase tracking-[0.12em] text-brand-500">
                                            {{ $product->sku }}
                                        </span>
                                        <span class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $product->name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $product->category->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        <span>HPP: <span class="text-gray-900 dark:text-white">Rp {{ number_format($product->base_price, 0, ',', '.') }}</span></span>
                                        <span>Harga Jual: <span class="text-brand-600 dark:text-brand-300">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span></span>
                                        <span>Ukuran: <span class="text-gray-900 dark:text-white">{{ number_format($product->length_cm, 2, ',', '.') }}</span> cm × <span class="text-gray-900 dark:text-white">{{ number_format($product->width_cm, 2, ',', '.') }}</span> cm</span>
                                        <span>Satuan: <span class="text-gray-900 dark:text-white">{{ $product->unit->name ?? '-' }}</span></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($product->productMaterials->isEmpty())
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Belum ada material</span>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($product->productMaterials as $productMaterial)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                                    {{ $productMaterial->material->name ?? '-' }}
                                                    @if ($productMaterial->material?->unit?->name)
                                                        <span class="text-[10px] text-brand-500/70 dark:text-brand-200/70">
                                                            ({{ $productMaterial->material->unit->name }})
                                                        </span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $product->description ? Str::limit($product->description, 80, '…') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        @can('product.edit')
                                            <a href="{{ route('products.edit', $product) }}"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('product.delete')
                                            <button type="button" @click="isConfirmOpen = true"
                                                class="inline-flex items-center gap-1 rounded-lg border border-error-200 px-3 py-2 text-xs font-semibold text-error-600 transition hover:border-error-300 hover:text-error-700 dark:border-error-500/60 dark:text-error-300 dark:hover:border-error-400 dark:hover:text-error-200">
                                                Hapus
                                            </button>

                                            <div x-cloak x-show="isConfirmOpen" x-transition.opacity
                                                class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60 p-4">
                                                <div x-transition.scale
                                                    class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-950">
                                                    <div class="flex items-start gap-3">
                                                        <span
                                                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-error-100 text-error-600 dark:bg-error-500/10 dark:text-error-200">
                                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M10 6.25V10m0 3.75h.008" stroke="currentColor"
                                                                    stroke-width="1.5" stroke-linecap="round" />
                                                                <circle cx="10" cy="10" r="7.25" stroke="currentColor"
                                                                    stroke-width="1.5" />
                                                            </svg>
                                                        </span>
                                                        <div class="space-y-2">
                                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                Hapus Produk?
                                                            </h3>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                Tindakan ini akan menghapus produk
                                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $product->name }}</span>
                                                                beserta keterkaitan materialnya.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="mt-6 flex items-center justify-end gap-2">
                                                        <button type="button" @click="isConfirmOpen = false"
                                                            class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-white">
                                                            Batal
                                                        </button>

                                                        <form action="{{ route('products.destroy', $product) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="inline-flex items-center rounded-lg bg-error-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-error-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error-500 focus-visible:ring-offset-2 dark:ring-offset-gray-900">
                                                                Ya, hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Data produk belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-4 dark:border-gray-900">
                {{ $products->links('components.pagination') }}
            </div>
        </div>
    </div>
@endsection
