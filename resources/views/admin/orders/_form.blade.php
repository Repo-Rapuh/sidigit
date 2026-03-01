@php
    $totals = $this->totalsPreview();
    $itemsCount = count($items ?? []);
    $totalQty = collect($items ?? [])->sum(fn ($item) => (float) ($item['qty'] ?? 0));
    $paidTotal = collect($payments ?? [])->sum(fn ($payment) => (float) ($payment['amount'] ?? 0));
    $balance = max(0, $totals['grand_total'] - $paidTotal);
    $change = max(0, $paidTotal - $totals['grand_total']);
    $isApprovalLocked = property_exists($this, 'isApprovalLocked') && (bool) $this->isApprovalLocked;
    $controlClass = 'mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $controlClassTight = $controlClass . ' h-11';
    $labelRowClass = 'flex items-center min-h-[20px]';
    $labelRowBetweenClass = $labelRowClass . ' justify-between';
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/40">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Order</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lengkapi data utama sebelum membuat item.</p>
            </div>
        </div>

        @if ($isApprovalLocked)
            <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
                Order dengan status <strong>Approval</strong> ke atas atau <strong>Dibatalkan</strong> bersifat <strong>read-only</strong> di halaman ini.
                Untuk perubahan status, gunakan aksi <strong>Ubah Status</strong> dari daftar order.
            </div>
        @endif

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="{{ $isApprovalLocked ? 'pointer-events-none opacity-70' : '' }}">
                <x-forms.searchable-select
                    label="Customer"
                    :options="$customers"
                    placeholder="Pilih customer"
                    wire:model="customer_id"
                    :button-class="$controlClassTight"
                />
                @error('customer_id')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="{{ $isApprovalLocked ? 'pointer-events-none opacity-70' : '' }}">
                @php
                    $statusOptions = collect([
                        ['value' => 'draft', 'label' => 'Draft'],
                        ['value' => 'quotation', 'label' => 'Quotation'],
                        ['value' => 'approval', 'label' => 'Approval Customer'],
                        ['value' => 'pembayaran', 'label' => 'Pembayaran'],
                        ['value' => 'desain', 'label' => 'Desain'],
                        ['value' => 'produksi', 'label' => 'Produksi'],
                        ['value' => 'qc', 'label' => 'QC'],
                        ['value' => 'siap', 'label' => 'Siap Diambil/Dikirim'],
                        ['value' => 'diambil', 'label' => 'Diambil'],
                        ['value' => 'selesai', 'label' => 'Selesai'],
                        ['value' => 'dibatalkan', 'label' => 'Dibatalkan'],
                    ]);
                @endphp
                <x-forms.searchable-select
                    label="Status"
                    :options="$statusOptions"
                    optionValue="value"
                    optionLabel="label"
                    placeholder="Pilih status"
                    wire:model.live="status"
                    :button-class="$controlClassTight"
                    required
                />
                @error('status')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Order <span class="text-red-500">*</span></label>
                <input type="date" wire:model="order_date" class="{{ $controlClassTight }}" required @disabled($isApprovalLocked) />
                @error('order_date')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline (Tanggal & Jam)</label>
                <input type="datetime-local" wire:model="deadline" class="{{ $controlClassTight }}" @disabled($isApprovalLocked) />
                @error('deadline')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
            <textarea wire:model="notes" rows="3" class="{{ $controlClass }}" @disabled($isApprovalLocked)></textarea>
            @error('notes')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/40">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Item Order</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan item sesuai produk dan bahan.</p>
            </div>
            @if (!$isApprovalLocked)
                <button type="button" wire:click="addItem"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                    + Tambah Item
                </button>
            @endif
        </div>

        @if ($isApprovalLocked)
            <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
                Item order dikunci mulai status <strong>Approval</strong>. Jika perlu revisi item, lakukan proses revisi terkontrol terlebih dahulu.
            </div>
        @endif

        <div class="mt-4 space-y-4 {{ $isApprovalLocked ? 'pointer-events-none opacity-70' : '' }}">
            @error('items')
                <p class="text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
            @foreach ($items as $index => $item)
                @php
                    $calc = $this->calculateItemPreview($item);
                    $selectedProduct = collect($products ?? [])->firstWhere('id', (int) ($item['product_id'] ?? 0));
                    $selectedProductType = (string) ($selectedProduct['product_type'] ?? 'goods');
                    $materialIds = $item['material_ids'] ?? ($selectedProduct['material_ids'] ?? []);
                    $materialOptions = !empty($materialIds)
                        ? collect($materialsAll ?? [])->whereIn('id', $materialIds)->values()->all()
                        : [];
                    $materialRequired = !empty($selectedProduct) && $selectedProductType === 'goods' && !empty($materialIds);
                    $showDimension = !empty($item['unit_id']) && in_array((int) $item['unit_id'], $dimensionUnitIds, true);
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40"
                    wire:key="order-item-{{ $index }}">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                        <div>
                            <div class="{{ $labelRowClass }}">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Produk <span class="text-red-500">*</span></label>
                            </div>
                            <x-forms.searchable-select
                                label=""
                                :options="$products"
                                placeholder="Pilih produk"
                                wire:model.live="items.{{ $index }}.product_id"
                                :button-class="$controlClassTight"
                            />
                            @error('items.' . $index . '.product_id')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                            @error('items.' . $index . '.unit_id')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="{{ $labelRowClass }}">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Bahan
                                    @if ($materialRequired)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                            </div>
                            <div wire:key="material-select-{{ $index }}-{{ $item['product_id'] ?? 'none' }}">
                                <x-forms.searchable-select
                                    label=""
                                    :options="$materialOptions"
                                    placeholder="Pilih bahan"
                                    wire:model.live="items.{{ $index }}.material_id"
                                    :button-class="$controlClassTight"
                                />
                            </div>
                            @if (empty($item['product_id']))
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Pilih produk terlebih dahulu untuk melihat bahan.
                                </p>
                            @elseif (empty($materialOptions))
                                @if ($selectedProductType === 'service')
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Produk jasa tidak membutuhkan bahan.
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                        Produk barang ini belum memiliki mapping bahan di master produk.
                                    </p>
                                @endif
                            @endif
                        </div>

                        <div>
                            <div class="{{ $labelRowClass }}">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Qty <span class="text-red-500">*</span></label>
                            </div>
                            <input type="number" step="1" min="1" inputmode="numeric"
                                wire:model.live="items.{{ $index }}.qty" required
                                class="{{ $controlClassTight }}" />
                            @error('items.' . $index . '.qty')
                                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="{{ $labelRowClass }}">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual</label>
                            </div>
                            <div class="" x-data="rupiahField(@entangle('items.' . $index . '.price').live)">
                                <input type="text" inputmode="numeric" x-model="display" @input="onInput"
                                    class="{{ $controlClassTight }}" />
                            </div>
                        </div>

                        <div class="flex items-start justify-end">
                            @if (!$isApprovalLocked)
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="mt-6 rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:text-red-300 dark:hover:bg-red-500/10">
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-5">
                        @if ($showDimension)
                            <div>
                                <div class="{{ $labelRowClass }}">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Panjang (cm)</label>
                                </div>
                                <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.length_cm"
                                    class="{{ $controlClassTight }}" />
                            </div>
                            <div>
                                <div class="{{ $labelRowClass }}">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Lebar (cm)</label>
                                </div>
                                <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.width_cm"
                                    class="{{ $controlClassTight }}" />
                            </div>
                        @else
                            <div class="hidden lg:block"></div>
                            <div class="hidden lg:block"></div>
                        @endif
                        <div>
                            <div class="{{ $labelRowClass }}">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Diskon</label>
                            </div>
                            <div class="" x-data="rupiahField(@entangle('items.' . $index . '.discount').live)">
                                <input type="text" inputmode="numeric" x-model="display" @input="onInput"
                                    class="{{ $controlClassTight }}" />
                            </div>
                        </div>
                        <div>
                            <div class="{{ $labelRowClass }}">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Finishing</label>
                            </div>
                            <livewire:components.searchable-multi-select
                                :options="$finishes"
                                optionLabelKey="name"
                                optionValueKey="id"
                                wire:model="items.{{ $index }}.finish_ids"
                                placeholder="Pilih finishing"
                                :wire:key="'finish-select-' . $index"
                                :button-class="$controlClassTight"
                            />
                        </div>
                        <div class="hidden lg:block"></div>
                        @if ($showDimension)
                            <div class="lg:col-start-1 lg:col-span-4 lg:pl-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Ukuran tetap diinput dalam cm. Harga jual otomatis memakai minimum penagihan 1 m2. Pemakaian bahan tetap dihitung aktual ke satuan dasar bahan (contoh: m2); jika material punya spec roll, perhitungan mengikuti lebar roll + waste.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-3 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                        <span>Total Item</span>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($calc['total'], 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

        @php
            $isEditing = property_exists($this, 'orderId') && !empty($this->orderId);
        @endphp
        <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/40">
            <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pembayaran</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catat cicilan atau pelunasan.</p>
            </div>
            @if (!$isEditing)
                <button type="button" wire:click="addPayment"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-900">
                    + Tambah Pembayaran
                </button>
            @else
                <a href="{{ route('orders.payments.create', ['order' => $orderId]) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-200 px-3 py-2 text-sm font-semibold text-brand-600 hover:bg-brand-50 dark:border-brand-700/50 dark:text-brand-300 dark:hover:bg-brand-500/10">
                    Input Pembayaran
                </a>
            @endif
            </div>

        <div class="mt-4 space-y-3">
            @foreach ($payments as $index => $payment)
                @php
                    $isHistory = $isEditing && !empty($payment['id']);
                @endphp
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                        @if ($isHistory)
                            <div class="{{ $controlClassTight }} flex items-center bg-gray-50 text-gray-700 dark:bg-gray-900/60 dark:text-gray-200">
                                Rp {{ number_format((float) ($payment['amount'] ?? 0), 0, ',', '.') }}
                            </div>
                        @else
                            <div class="" x-data="rupiahField(@entangle('payments.' . $index . '.amount').live)">
                                <input type="text" inputmode="numeric" x-model="display" @input="onInput" class="{{ $controlClassTight }}" />
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Metode</label>
                        @php
                            $paymentMethods = collect([
                                ['value' => 'cash', 'label' => 'Cash'],
                                ['value' => 'transfer', 'label' => 'Transfer'],
                                ['value' => 'qris', 'label' => 'QRIS'],
                            ]);
                        @endphp
                        @if ($isHistory)
                            <div class="{{ $controlClassTight }} flex items-center bg-gray-50 text-gray-700 dark:bg-gray-900/60 dark:text-gray-200">
                                {{ $paymentMethods->firstWhere('value', $payment['method'] ?? null)['label'] ?? '-' }}
                            </div>
                        @else
                            <x-forms.searchable-select
                                label=""
                                :options="$paymentMethods"
                                optionValue="value"
                                optionLabel="label"
                                placeholder="Pilih metode"
                                wire:model="payments.{{ $index }}.method"
                                :button-class="$controlClassTight"
                            />
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                        @if ($isHistory)
                            <div class="{{ $controlClassTight }} flex items-center bg-gray-50 text-gray-700 dark:bg-gray-900/60 dark:text-gray-200">
                                {{ $payment['paid_at'] ?? '-' }}
                            </div>
                        @else
                            <input type="datetime-local" wire:model="payments.{{ $index }}.paid_at" class="{{ $controlClassTight }}" />
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                        @if ($isHistory)
                            <div class="{{ $controlClassTight }} flex items-center bg-gray-50 text-gray-700 dark:bg-gray-900/60 dark:text-gray-200">
                                {{ $payment['notes'] ?? '-' }}
                            </div>
                        @else
                            <input type="text" wire:model="payments.{{ $index }}.notes" class="{{ $controlClassTight }}" />
                        @endif
                    </div>
                    <div class="flex items-end justify-end">
                        @if (!$isHistory && !$isEditing)
                            <button type="button" wire:click="removePayment({{ $index }})"
                                class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:text-red-300 dark:hover:bg-red-500/10">
                                Hapus
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/40">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ringkasan</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ikhtisar total pesanan dan pembayaran.</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Harga</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($totals['total_price'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500 dark:text-gray-400">Diskon</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($totals['total_discount'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500 dark:text-gray-400">Grand Total</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($totals['grand_total'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-5">
            <div class="rounded-xl border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                <div class="flex items-center justify-between">
                    <span>Total Item</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $itemsCount }} item</span>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                <div class="flex items-center justify-between">
                    <span>Total Qty</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($totalQty, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                <div class="flex items-center justify-between">
                    <span>Total Dibayar</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($paidTotal, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                <div class="flex items-center justify-between">
                    <span>Sisa Tagihan</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                <div class="flex items-center justify-between">
                    <span>Kembalian</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($change, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
