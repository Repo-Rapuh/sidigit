<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card class="rounded-3xl p-4 shadow-theme-sm">
        @if (!empty($isApprovalLocked))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
                Mode lihat aktif untuk order status Approval ke atas. Perubahan status dilakukan dari aksi <strong>Ubah Status</strong> pada daftar order.
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-8">
            <div class="sticky top-20 z-30 -mx-2 rounded-2xl border border-gray-200/80 bg-white/95 p-2 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if (!empty($isApprovalLocked))
                            <a href="{{ route('orders.invoice', ['order' => $orderId]) }}" class="btn btn-secondary inline-flex items-center gap-2">
                                <x-lucide-file-text class="h-4 w-4" />
                                <span>Lihat Invoice</span>
                            </a>
                            <a href="{{ route('orders.quotation', ['order' => $orderId]) }}" class="btn btn-secondary inline-flex items-center gap-2">
                                <x-lucide-eye class="h-4 w-4" />
                                <span>Lihat Quotation</span>
                            </a>
                            <a href="{{ route('orders.invoice', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                                class="btn btn-secondary inline-flex items-center gap-2">
                                <x-lucide-printer class="h-4 w-4" />
                                <span>Print Invoice</span>
                            </a>
                            <a href="{{ route('orders.quotation', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                                class="btn btn-secondary inline-flex items-center gap-2">
                                <x-lucide-printer class="h-4 w-4" />
                                <span>Print Quotation</span>
                            </a>
                            <a href="{{ route('orders.payments.create', ['order' => $orderId]) }}" class="btn btn-secondary inline-flex items-center gap-2">
                                <x-lucide-wallet class="h-4 w-4" />
                                <span>Input Pembayaran</span>
                            </a>
                            <a href="{{ route('orders.receipt', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                                class="btn btn-secondary inline-flex items-center gap-2">
                                <x-lucide-printer class="h-4 w-4" />
                                <span>Print Struk</span>
                            </a>
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary inline-flex items-center gap-2">
                                <x-lucide-arrow-left class="h-4 w-4" />
                                <span>Kembali</span>
                            </a>
                        @else
                            @if (($status ?? 'draft') === 'draft')
                                <button type="button" wire:click="makeQuotation" class="btn btn-secondary">
                                    Buat Quotation
                                </button>
                            @endif
                            @if (($status ?? 'draft') === 'quotation')
                                <button type="button" wire:click="approveQuotation" class="btn btn-secondary">
                                    Approve Quotation
                                </button>
                            @endif
                            @if (!in_array(($status ?? 'draft'), ['draft', 'quotation'], true))
                                <a href="{{ route('orders.invoice', ['order' => $orderId]) }}" class="btn btn-secondary">
                                    Lihat Invoice
                                </a>
                            @endif
                            <a href="{{ route('orders.payments.create', ['order' => $orderId]) }}" class="btn btn-secondary">
                                Input Pembayaran
                            </a>
                            <a href="{{ route('orders.receipt', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                                class="btn btn-secondary">
                                Print Struk
                            </a>
                            @if (($status ?? 'draft') !== 'draft')
                                <a href="{{ route('orders.quotation', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                                    class="btn btn-secondary">
                                    Print Quotation
                                </a>
                            @endif
                            @if (!in_array(($status ?? 'draft'), ['draft', 'quotation'], true))
                                <a href="{{ route('orders.invoice', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                                    class="btn btn-secondary">
                                    Print Invoice
                                </a>
                            @endif
                            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                                <span wire:loading.remove wire:target="save">Simpan</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
                            </button>
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Kembali</a>
                        @endif
                    </div>
                </div>
            </div>

            @include('admin.orders._form')
        </form>
    </x-card>
</div>
