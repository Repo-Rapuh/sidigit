<div class="space-y-4">
    @if ($modalMode === 'assign')
        <div>
            <x-forms.searchable-select
                label="Role Penanggung Jawab"
                :options="$this->roleOptions"
                optionValue="id"
                optionLabel="label"
                placeholder="Pilih role"
                wire:model="assign_role_id"
            />
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Assignment menggunakan role agar distribusi pekerjaan tetap fleksibel per tim.
            </p>
        </div>
    @elseif ($modalMode === 'qc_fail')
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan QC Gagal</label>
            <textarea wire:model="qc_fail_note" rows="4" class="form-input mt-2" placeholder="Contoh: warna tidak presisi, perlu cetak ulang."></textarea>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Job akan kembali ke status <strong>Produksi</strong> dan status order otomatis kembali ke <strong>Produksi</strong>.
            </p>
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-gray-50/90 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tracking ID</p>
                    <h3 class="mt-1.5 text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $historyMeta['tracking_id'] ?? '-' }}</h3>
                    <p class="mt-1.5 text-sm leading-5 text-gray-500 dark:text-gray-400">
                        {{ $historyMeta['order_no'] ?? '-' }} · {{ $historyMeta['item_name'] ?? '-' }} (Qty {{ $historyMeta['qty'] ?? '-' }})
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $historyMeta['status_badge_class'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $historyMeta['status_label'] ?? '-' }}
                </span>
            </div>

            <div class="mt-4 max-h-[58vh] space-y-4 overflow-y-auto pr-1">
                @forelse ($historyLogs as $log)
                    <div class="flex gap-3">
                        <div class="relative flex w-9 shrink-0 justify-center">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 {{ !empty($log['is_active']) ? 'border-brand-400 bg-brand-50 text-brand-600 dark:border-brand-300 dark:bg-brand-500/20 dark:text-brand-300' : 'border-gray-300 bg-white text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                <x-dynamic-component :component="'lucide-' . ($log['icon'] ?? 'circle')" class="h-3.5 w-3.5" />
                            </div>
                            @if (!$loop->last)
                                <div class="absolute top-9 h-8 border-l-2 {{ !empty($log['is_active']) ? 'border-brand-400/70' : 'border-gray-300 dark:border-gray-700' }}"></div>
                            @endif
                        </div>
                        <div class="flex-1 pt-0.5 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $log['date'] ?? '-' }}</p>
                                    <h4 class="mt-0.5 text-base font-semibold leading-tight text-gray-900 dark:text-white">{{ $log['label'] ?? '-' }}</h4>
                                    <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $log['subtitle'] ?? '-' }}</p>
                                </div>
                                <span class="mt-0.5 min-w-[44px] text-right text-xs font-semibold tabular-nums text-gray-400 dark:text-gray-500">
                                    {{ $log['time'] ?? '--:--' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada riwayat untuk job ini.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
