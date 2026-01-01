@props([
    'items' => [],
])

{{-- border border-gray-200 bg-white p-3  --}}
<nav class="mt-2 flex rounded-lg w-full mb-6 px-4 text-gray-600 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300"
    aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
        @foreach ($items as $item)
            @php
                $isCurrent = $item['current'] ?? $loop->last;
                $isHome = $item['icon'] ?? false;
            @endphp

            <li @if ($isCurrent) aria-current="page" @endif>
                <div class="flex items-center space-x-1.5 rtl:space-x-reverse">
                    @if (!$loop->first)
                        <svg class="h-3.5 w-3.5 rtl:rotate-180 text-gray-500 dark:text-gray-400" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m9 5 7 7-7 7" />
                        </svg>
                    @endif

                    @if (!empty($item['url']) && !$isCurrent)
                        <a href="{{ $item['url'] }}"
                            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-brand-500 dark:text-gray-300 dark:hover:text-brand-300">
                            @if ($isHome)
                                <svg class="me-1.5 h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5" />
                                </svg>
                            @endif
                            {{ $item['label'] ?? '' }}
                        </a>
                    @else
                        <span class="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
                            @if ($isHome)
                                <svg class="me-1.5 h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5" />
                                </svg>
                            @endif
                            {{ $item['label'] ?? '' }}
                        </span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
