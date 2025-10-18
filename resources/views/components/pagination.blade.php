@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex justify-center">
        <ul class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-300">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-gray-400 cursor-not-allowed dark:border-gray-700 dark:text-gray-600">
                        Prev
                    </span>
                </li>
            @else
                <li>
                    <a class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-gray-600 transition hover:border-brand-200 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400"
                        href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        Prev
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li aria-disabled="true">
                        <span class="inline-flex items-center rounded-xl border border-dashed border-gray-200 px-3 py-2 text-gray-400 dark:border-gray-700 dark:text-gray-600">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page">
                                <span class="inline-flex items-center rounded-xl border border-brand-500 bg-brand-500 px-3 py-2 text-white shadow-sm dark:border-brand-400 dark:bg-brand-400">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-gray-600 transition hover:border-brand-200 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400"
                                    href="{{ $url }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-gray-600 transition hover:border-brand-200 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400"
                        href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Next
                    </a>
                </li>
            @else
                <li aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-gray-400 cursor-not-allowed dark:border-gray-700 dark:text-gray-600">
                        Next
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
