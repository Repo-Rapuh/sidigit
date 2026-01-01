@php
    $hasDetail = $row->properties?->has('old') || $row->properties?->has('attributes');
@endphp

@if ($hasDetail)
    <div x-data="{ isDetailOpen: false }" @keydown.escape.window="isDetailOpen = false">
        <button type="button" @click="isDetailOpen = true"
            class="inline-flex items-center gap-2 rounded-lg border border-brand-200 px-3 py-2 text-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:text-brand-700 dark:border-brand-600/40 dark:text-brand-300 dark:hover:border-brand-500 dark:hover:text-brand-200">
            Lihat
        </button>

        <div x-cloak x-show="isDetailOpen" x-transition.opacity
            class="fixed inset-0 z-[100000] flex items-center justify-center bg-gray-950/90 px-4"
            role="dialog" aria-modal="true" aria-labelledby="activityDetailTitle{{ $row->id }}"
            @click.self="isDetailOpen = false">
            <div x-show="isDetailOpen" x-transition.scale
                class="w-full max-w-4xl rounded-3xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="relative w-full">
                    <button type="button" @click="isDetailOpen = false"
                        class="absolute right-0 top-0 inline-flex items-center justify-center rounded-full border border-gray-200 p-2 text-gray-500 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-white">
                        <svg class="h-4 w-4" viewBox="0 0 14 14" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="m3 3 8 8M3 11 11 3" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div class="flex flex-col items-center gap-3 text-center">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 id="activityDetailTitle{{ $row->id }}"
                                class="text-lg font-semibold text-gray-900 dark:text-white">
                                Detail Perubahan
                            </h2>
                            <p class="text-sm text-gray-500 text-center dark:text-gray-400">
                                {{ $row->created_at?->format('d M Y, H:i') }} •
                                {{ $row->causer?->name ?? 'Sistem' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    @if ($row->properties?->has('old'))
                        <div
                            class="space-y-3 rounded-2xl border border-gray-200 bg-gray-50/40 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Data Lama
                            </h3>
                            <div
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-950 shadow-inner dark:border-gray-800">
                                <div class="flex items-center gap-2 border-b border-gray-800/60 bg-gray-900 px-4 py-3">
                                    <span class="h-2.5 w-2.5 rounded-full bg-error-400"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-warning-400"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-success-400"></span>
                                    <span class="ml-3 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        JSON
                                    </span>
                                </div>
                                <div class="max-h-80 overflow-auto bg-gray-950">
                                    <pre class="whitespace-pre-wrap px-4 py-4 text-xs font-mono leading-6 text-gray-100">{{ trim(json_encode($row->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($row->properties?->has('attributes'))
                        <div
                            class="space-y-3 rounded-2xl border border-gray-200 bg-gray-50/40 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Data Baru
                            </h3>
                            <div
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-950 shadow-inner dark:border-gray-800">
                                <div class="flex items-center gap-2 border-b border-gray-800/60 bg-gray-900 px-4 py-3">
                                    <span class="h-2.5 w-2.5 rounded-full bg-error-400"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-warning-400"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-success-400"></span>
                                    <span class="ml-3 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        JSON
                                    </span>
                                </div>
                                <div class="max-h-80 overflow-auto bg-gray-950">
                                    <pre class="whitespace-pre-wrap px-4 py-4 text-xs font-mono leading-6 text-gray-100">{{ trim(json_encode($row->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="isDetailOpen = false"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@else
    <span class="text-gray-400">-</span>
@endif
