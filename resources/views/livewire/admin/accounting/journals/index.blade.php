@php
    $totalDebit = collect($lines ?? [])->sum(fn ($line) => (float) ($line['debit'] ?? 0));
    $totalCredit = collect($lines ?? [])->sum(fn ($line) => (float) ($line['credit'] ?? 0));
    $isBalanced = abs($totalDebit - $totalCredit) < 0.009 && $totalDebit > 0;
@endphp

<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card :title="$title" :description="$description">
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Jurnal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model.live="journal_date" class="form-input mt-3" />
                    @error('journal_date')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Keterangan</label>
                    <input type="text" wire:model.blur="entry_description" class="form-input mt-3"
                        placeholder="Contoh: Penyesuaian kas harian" />
                    @error('entry_description')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Akun</th>
                            <th class="px-3 py-2 text-left">Deskripsi Baris</th>
                            <th class="px-3 py-2 text-right">Debit</th>
                            <th class="px-3 py-2 text-right">Kredit</th>
                            <th class="px-3 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($lines as $index => $line)
                            <tr wire:key="journal-line-{{ $index }}">
                                <td class="px-3 py-2 align-top">
                                    <select wire:model.live="lines.{{ $index }}.account_id" class="form-input">
                                        <option value="">Pilih akun</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account['id'] }}">{{ $account['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("lines.$index.account_id")
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input type="text" wire:model.blur="lines.{{ $index }}.description" class="form-input"
                                        placeholder="Opsional" />
                                    @error("lines.$index.description")
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input type="number" step="0.01" min="0"
                                        wire:model.blur="lines.{{ $index }}.debit"
                                        class="form-input text-right" placeholder="0" />
                                    @error("lines.$index.debit")
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input type="number" step="0.01" min="0"
                                        wire:model.blur="lines.{{ $index }}.credit"
                                        class="form-input text-right" placeholder="0" />
                                    @error("lines.$index.credit")
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2 text-right align-top">
                                    <button type="button" wire:click="removeLine({{ $index }})"
                                        class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-900/20">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <td colspan="2" class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-200">Total</td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white">
                                {{ number_format($totalDebit, 2, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white">
                                {{ number_format($totalCredit, 2, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span
                                    class="inline-flex rounded-md px-2 py-1 text-xs font-semibold {{ $isBalanced ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                    {{ $isBalanced ? 'Balance' : 'Belum Balance' }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @error('lines')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap items-center justify-between gap-3">
                <button type="button" wire:click="addLine" class="btn btn-secondary">
                    + Tambah Baris
                </button>

                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan Jurnal</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </x-card>

    <x-card title="Riwayat Jurnal Terakhir" description="Menampilkan 12 jurnal terbaru pada cabang aktif.">
        <div class="space-y-4">
            @forelse ($recentJournals as $journal)
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No Jurnal</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $journal->journal_no }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $journal->journal_date?->format('d M Y') }}</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Debit {{ number_format((float) $journal->total_debit, 2, ',', '.') }} |
                                Kredit {{ number_format((float) $journal->total_credit, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    @if (!empty($journal->description))
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $journal->description }}</p>
                    @endif

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-1 pr-3">Akun</th>
                                    <th class="py-1 pr-3">Deskripsi</th>
                                    <th class="py-1 pr-3 text-right">Debit</th>
                                    <th class="py-1 text-right">Kredit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($journal->lines as $line)
                                    <tr>
                                        <td class="py-1 pr-3 text-gray-800 dark:text-gray-100">
                                            {{ $line->account?->code }} - {{ $line->account?->name }}
                                        </td>
                                        <td class="py-1 pr-3 text-gray-600 dark:text-gray-300">{{ $line->description ?? '-' }}</td>
                                        <td class="py-1 pr-3 text-right text-gray-700 dark:text-gray-200">
                                            {{ (float) $line->debit > 0 ? number_format((float) $line->debit, 2, ',', '.') : '-' }}
                                        </td>
                                        <td class="py-1 text-right text-gray-700 dark:text-gray-200">
                                            {{ (float) $line->credit > 0 ? number_format((float) $line->credit, 2, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Belum ada jurnal pada cabang aktif.
                </div>
            @endforelse
        </div>
    </x-card>
</div>
