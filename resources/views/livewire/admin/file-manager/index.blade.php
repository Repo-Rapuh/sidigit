@php
    $formatBytes = function (int $bytes): string {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 2, ',', '.') . ' ' . $units[$power];
    };

    $usedPercent = is_null($storageDetails['used_percent'] ?? null)
        ? null
        : (float) ($storageDetails['used_percent'] ?? 0);

    $progressBarClass = 'bg-brand-500';
    if (!is_null($usedPercent)) {
        if ($usedPercent >= 90) {
            $progressBarClass = 'bg-red-500';
        } elseif ($usedPercent >= 70) {
            $progressBarClass = 'bg-amber-500';
        } else {
            $progressBarClass = 'bg-emerald-500';
        }
    }
@endphp

<div class="space-y-6">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card :title="$title" :description="$description">
        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Cabang</label>
                    <select wire:model.live="branch_id" class="form-input mt-2">
                        @forelse ($branchOptions as $branch)
                            <option value="{{ $branch['id'] }}">{{ $branch['name'] }}</option>
                        @empty
                            <option value="">Tidak ada cabang</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Folder</label>
                    <select wire:model.live="folder" class="form-input mt-2">
                        <option value="all">Semua Folder</option>
                        @foreach ($folderOptions as $folderItem)
                            <option value="{{ $folderItem }}">{{ $folderItem }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Cari</label>
                    <input type="text" wire:model.live.debounce.400ms="search" class="form-input mt-2"
                        placeholder="Nama file / path..." />
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Per halaman</label>
                    <select wire:model.live="perPage" class="form-input mt-2">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300">
                Prefix aktif:
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ !empty($branch_id) ? $branch_id . '/' : '-' }}
                </span>
                · Total file:
                <span class="font-semibold text-gray-900 dark:text-white">{{ $files->total() }}</span>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/20">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Storage Details</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kuota global klien (akumulasi semua cabang).</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Used Storage (Global)</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $formatBytes((int) ($storageDetails['client_total_size'] ?? 0)) }}
                            @if ((int) $storageDetails['quota_bytes'] > 0)
                                / {{ $formatBytes((int) $storageDetails['quota_bytes']) }}
                            @endif
                        </p>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                            Cabang aktif: {{ $formatBytes((int) ($storageDetails['branch_total_size'] ?? 0)) }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="h-full rounded-full transition-all {{ $progressBarClass }}"
                        style="width: {{ $storageDetails['used_percent'] ?? 0 }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>
                        @if (!is_null($storageDetails['used_percent']))
                            {{ number_format((float) $storageDetails['used_percent'], 2, ',', '.') }}% terpakai
                        @else
                            Quota belum diatur (`UPLOAD_QUOTA_BYTES=0`)
                        @endif
                    </span>
                    @if (!is_null($storageDetails['remaining_bytes']))
                        <span>Sisa: {{ $formatBytes((int) $storageDetails['remaining_bytes']) }}</span>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total File</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format((int) $storageDetails['total_files'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Gambar</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format((int) $storageDetails['image_count'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format((int) $storageDetails['document_count'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Lainnya</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format((int) $storageDetails['other_count'], 0, ',', '.') }}</p>
                    </div>
                </div>

            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">File</th>
                            <th class="px-3 py-2">Folder</th>
                            <th class="px-3 py-2">Tipe</th>
                            <th class="px-3 py-2 text-right no-wrap">Ukuran</th>
                            <th class="px-3 py-2">Diubah</th>
                            <th class="px-3 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950/20">
                        @forelse ($files as $file)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-3">
                                        @if (!empty($file['is_image']) && !empty($file['preview_url']))
                                            <img src="{{ $file['preview_url'] }}" alt="{{ $file['filename'] }}"
                                                loading="lazy" decoding="async"
                                                class="h-9 w-9 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                                        @else
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-[10px] font-semibold uppercase text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                                                {{ $file['extension'] }}
                                            </div>
                                        @endif

                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-gray-900 dark:text-white">{{ $file['filename'] }}</p>
                                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $file['path'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $file['directory'] }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $file['mime'] }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap tabular-nums text-gray-700 dark:text-gray-200">{{ $formatBytes((int) $file['size']) }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                    {{ !empty($file['updated_at']) ? $file['updated_at']->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-2" x-data="{
                                        fileUrl: @js($file['url']),
                                        copied: false,
                                        copyLink() {
                                            if (!this.fileUrl) return;
                                            if (navigator.clipboard?.writeText && window.isSecureContext) {
                                                navigator.clipboard.writeText(this.fileUrl)
                                                    .then(() => this.showCopied())
                                                    .catch(() => this.fallbackCopy());
                                                return;
                                            }
                                            this.fallbackCopy();
                                        },
                                        fallbackCopy() {
                                            const area = document.createElement('textarea');
                                            area.value = this.fileUrl;
                                            area.setAttribute('readonly', 'readonly');
                                            area.style.position = 'fixed';
                                            area.style.top = '-9999px';
                                            area.style.left = '-9999px';
                                            document.body.appendChild(area);
                                            area.focus();
                                            area.select();

                                            let copied = false;
                                            try {
                                                copied = document.execCommand('copy');
                                            } catch (e) {
                                                copied = false;
                                            } finally {
                                                document.body.removeChild(area);
                                            }

                                            if (copied) {
                                                this.showCopied();
                                                return;
                                            }

                                            window.prompt('Salin URL file ini:', this.fileUrl);
                                        },
                                        showCopied() {
                                            this.copied = true;
                                            window.dispatchEvent(new CustomEvent('toast', {
                                                detail: {
                                                    message: 'URL file berhasil disalin.',
                                                    type: 'success',
                                                    duration: 2200,
                                                }
                                            }));
                                            setTimeout(() => this.copied = false, 1200);
                                        }
                                    }">
                                        @if (!empty($file['url']))
                                            <a href="{{ $file['url'] }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                                Lihat
                                            </a>
                                        @else
                                            <span
                                                class="inline-flex items-center rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-400 dark:border-gray-800 dark:text-gray-500">
                                                Lihat
                                            </span>
                                        @endif

                                        <a href="{{ route('file-manager.download', ['path' => base64_encode($file['path'])]) }}"
                                            class="inline-flex items-center rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                            Download
                                        </a>

                                        <button type="button" @click="copyLink()" @disabled(empty($file['url']))
                                            class="inline-flex items-center whitespace-nowrap rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                            <span x-text="copied ? 'Tersalin' : 'Salin URL'"></span>
                                        </button>

                                        @can('file-manager.delete')
                                            <button type="button"
                                                wire:click="delete('{{ base64_encode($file['path']) }}')"
                                                wire:confirm="Hapus file ini?"
                                                class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                Hapus
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada file pada prefix/folder ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $files->links() }}
            </div>
        </div>
    </x-card>
</div>
