<div class="space-y-2"
    x-data="{
        draggingJobId: null,
        draggingFromStatus: null,
        overStatus: null,
        startDrag(event, jobId, fromStatus) {
            this.draggingJobId = jobId;
            this.draggingFromStatus = fromStatus;
            event.dataTransfer.effectAllowed = 'move';
        },
        endDrag() {
            this.draggingJobId = null;
            this.draggingFromStatus = null;
            this.overStatus = null;
        },
        dropTo(toStatus) {
            if (!this.draggingJobId) return;
            $wire.moveCard(this.draggingJobId, toStatus);
            this.endDrag();
        },
    }">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Produksi Kanban</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Alur: Antrian -> Desain -> Produksi -> QC -> Siap Diambil. Tahap desain opsional (bisa langsung ke Produksi).
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('productions.history') }}" class="btn btn-secondary inline-flex items-center gap-2">
                    <x-lucide-history class="h-4 w-4" />
                    <span>Riwayat</span>
                </a>
            </div>
        </div>

        <div class="mt-4">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-input"
                placeholder="Cari order no, nama produk, atau nama user claim...">
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Drag card ke kolom berikutnya untuk ubah status. QC gagal tetap melalui tombol agar alasan revisi tercatat.
            </p>
            @if ($this->roleScopeLabel())
                <p class="mt-1 text-xs font-medium text-brand-600 dark:text-brand-300">
                    {{ $this->roleScopeLabel() }}
                </p>
            @endif
        </div>

        <div class="mt-6 overflow-x-auto pb-3">
            <div class="grid min-w-max grid-flow-col auto-cols-[22rem] gap-5 xl:auto-cols-[23rem]">
                @foreach ($this->columns as $columnKey => $column)
                    <div class="flex min-h-[620px] flex-col rounded-2xl border bg-gray-50/60 {{ $column['accent'] }} transition dark:bg-gray-900/40"
                        x-bind:class="overStatus === '{{ $columnKey }}' ? 'ring-2 ring-brand-400/60' : ''"
                        x-on:dragenter.prevent="overStatus = '{{ $columnKey }}'"
                        x-on:dragover.prevent
                        x-on:dragleave="if (overStatus === '{{ $columnKey }}') overStatus = null"
                        x-on:drop.prevent="dropTo('{{ $columnKey }}')">
                        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $column['label'] }}</h3>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                {{ count($this->groupedJobs[$columnKey] ?? []) }}
                            </span>
                        </div>

                        <div class="flex-1 space-y-3 overflow-y-auto p-3">
                            @forelse (($this->groupedJobs[$columnKey] ?? []) as $job)
                                @php
                                    $priority = $this->priorityMeta($job);
                                    $claim = $this->claimMeta($job);
                                @endphp
                                <article class="cursor-move rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                                    draggable="true"
                                    x-on:dragstart="startDrag($event, {{ $job->id }}, '{{ $job->status }}')"
                                    x-on:dragend="endDrag()">
                                    <div class="mb-2 flex items-start justify-between gap-2">
                                        <div>
                                            <a href="{{ route('orders.edit', ['order' => $job->order_id]) }}"
                                                class="text-sm font-semibold text-brand-600 hover:underline">
                                                {{ $job->order?->order_no ?? '-' }}
                                            </a>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $job->orderItem?->product?->name ?? '-' }}
                                                · Qty {{ number_format((float) ($job->orderItem?->qty ?? 0), 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priority['class'] }}">
                                                {{ $priority['label'] }}
                                            </span>
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                {{ \App\Models\ProductionJob::stageOptions()[$job->stage] ?? ucfirst($job->stage) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center justify-between gap-2">
                                            <span>Deadline</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-200">
                                                {{ $this->deadlineDisplay($job) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span>Countdown</span>
                                            <span class="font-medium {{ $priority['label'] === 'Urgent' ? 'text-red-600 dark:text-red-300' : 'text-gray-700 dark:text-gray-200' }}">
                                                {{ $this->deadlineCountdown($job) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span>Bahan</span>
                                            <span class="truncate font-medium text-gray-700 dark:text-gray-200">{{ $job->orderItem?->material?->name ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span>Ukuran</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $this->sizeLabel($job) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span>Role</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $job->assignedRole?->name ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span>PIC</span>
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $claim['class'] }}">
                                                {{ $claim['label'] }}
                                            </span>
                                        </div>
                                        <div>Update: {{ $job->updated_at?->format('d M H:i') ?? '-' }}</div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if ($columnKey === \App\Models\ProductionJob::STATUS_ANTRIAN)
                                            @can('production.edit')
                                                @if ($this->canClaim($job) && empty($job->claimed_by))
                                                    <button type="button" wire:click="claim({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        Ambil Task
                                                    </button>
                                                @elseif ($this->canStartFromQueue($job))
                                                    <button type="button" wire:click="moveCard({{ $job->id }}, '{{ $this->queueStartTarget($job) }}')" class="btn btn-primary text-xs">
                                                        {{ $this->queueStartLabel($job) }}
                                                    </button>
                                                @endif
                                            @endcan
                                        @elseif ($columnKey === 'desain')
                                            @can('production.edit')
                                                @if ($this->canClaim($job) && empty($job->claimed_by))
                                                    <button type="button" wire:click="claim({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        Ambil Task
                                                    </button>
                                                @endif
                                                @if (!empty($job->claimed_by) && $this->canMove($job))
                                                    <button type="button" wire:click="moveCard({{ $job->id }}, 'in_progress')" class="btn btn-primary text-xs">
                                                        Lanjut Produksi
                                                    </button>
                                                @endif
                                            @endcan
                                        @elseif ($columnKey === \App\Models\ProductionJob::STATUS_IN_PROGRESS)
                                            @can('production.edit')
                                                @if ($this->canClaim($job) && empty($job->claimed_by))
                                                    <button type="button" wire:click="claim({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        Ambil Task
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('production.qc')
                                                @if (!empty($job->claimed_by) && $this->canMove($job))
                                                    <button type="button" wire:click="moveToQc({{ $job->id }})" class="btn btn-primary text-xs">
                                                        Kirim QC
                                                    </button>
                                                @endif
                                            @endcan
                                        @elseif ($columnKey === \App\Models\ProductionJob::STATUS_QC)
                                            @can('production.qc')
                                                @if ($this->canMove($job))
                                                    <button type="button" wire:click="qcPass({{ $job->id }})" class="btn btn-primary text-xs">
                                                        QC Lulus
                                                    </button>
                                                    <button type="button" wire:click="openQcFail({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        QC Gagal
                                                    </button>
                                                @endif
                                            @endcan
                                        @endif

                                        @can('production.edit')
                                            @if ($this->canRelease($job))
                                                <button type="button" wire:click="release({{ $job->id }})" class="btn btn-secondary text-xs">
                                                    Lepas
                                                </button>
                                            @endif
                                        @endcan

                                        <button type="button" wire:click="openTaskDetail({{ $job->id }})" class="btn btn-secondary text-xs">
                                            Detail
                                        </button>
                                        <a href="{{ route('orders.edit', ['order' => $job->order_id]) }}" class="btn btn-secondary text-xs">
                                            Lihat Order
                                        </a>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    Belum ada task.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <x-modal wire:model="showQcFailModal" maxWidth="md">
        <x-slot name="header">QC Gagal</x-slot>

        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Alasan QC Gagal</label>
            <textarea wire:model="qcFailNote" rows="4" class="form-input" placeholder="Contoh: warna tidak presisi, cetak ulang."></textarea>
            @error('qcFailNote')
                <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-slot name="footer">
            <button type="button" wire:click="closeModal" class="btn btn-secondary">Batal</button>
            <button type="button" wire:click="saveQcFail" class="btn btn-primary">Kembalikan ke Produksi</button>
        </x-slot>
    </x-modal>

    <x-modal wire:model="showTaskDetailModal" maxWidth="2xl">
        <x-slot name="header">Detail Task Produksi</x-slot>

        @php
            $detail = $taskDetail ?? [];
        @endphp

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/60">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tracking ID</p>
                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $detail['tracking_id'] ?? '-' }}</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $detail['order_no'] ?? '-' }} · {{ $detail['customer_name'] ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status Board</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $detail['status'] ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900/40">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Produk</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $detail['product_name'] ?? '-' }}</p>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">Qty: {{ $detail['qty'] ?? '-' }}</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">Bahan: {{ $detail['material'] ?? '-' }}</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">Ukuran: {{ $detail['size'] ?? '-' }}</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900/40">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Prioritas</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $detail['priority'] ?? '-' }}</p>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">Deadline: {{ $detail['deadline'] ?? '-' }}</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">{{ $detail['deadline_countdown'] ?? '-' }}</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">PIC: {{ $detail['claim'] ?? '-' }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Finishing</p>
                @if (!empty($detail['finishes'] ?? []))
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach (($detail['finishes'] ?? []) as $finishName)
                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                {{ $finishName }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tidak ada finishing.</p>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900/40">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan Order</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ ($detail['order_notes'] ?? null) ?: '-' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900/40">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan Produksi</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ ($detail['job_notes'] ?? null) ?: '-' }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-3 dark:border-gray-700 dark:bg-gray-900/40">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Lampiran File</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada lampiran file pada task ini. Untuk sementara cek file referensi pada detail order.
                </p>
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" wire:click="closeTaskDetail" class="btn btn-secondary">Tutup</button>
        </x-slot>
    </x-modal>
</div>
