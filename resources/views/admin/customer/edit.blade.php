@extends('layouts.app')

@section('title', 'Edit Pelanggan')

@section('content')
    <div class="space-y-6">
        <div class="flex items-baseline justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Pelanggan</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Perbarui data pelanggan agar tetap akurat.
                </p>
            </div>
            <a href="{{ route('customers.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                Kembali
            </a>
        </div>

        <div class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
            @if (session('error'))
                <div
                    class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm font-medium text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                @include('admin.customer._form', ['customer' => $customer, 'memberTypes' => $memberTypes])

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-950">
                        Perbarui Pelanggan
                    </button>
                    <a href="{{ route('customers.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
