@props(['paginator'])


<div class="flex flex-wrap items-center justify-between gap-3">
    <!-- Info -->
    <div class="text-sm text-gray-600 dark:text-gray-400">
        Showing {{ $paginator->firstItem() ?? 0 }}
        -
        {{ $paginator->lastItem() ?? 0 }}
        of {{ $paginator->total() }}
    </div>

    @if ($paginator->hasPages())
        <div class="flex flex-wrap items-center gap-3">
            <!-- Pagination buttons -->
            <nav class="inline-flex items-center gap-1">
                @php
                    $lastPage = $paginator->lastPage();
                    $current = $paginator->currentPage();
                    $pages = [];

                    if ($lastPage <= 10) {
                        $pages = range(1, $lastPage);
                    } else {
                        $pages = [1, $lastPage];
                        $windowStart = max(2, $current - 2);
                        $windowEnd = min($lastPage - 1, $current + 2);

                        if ($windowStart <= 3) {
                            $windowStart = 2;
                            $windowEnd = 5;
                        }

                        if ($windowEnd >= $lastPage - 2) {
                            $windowEnd = $lastPage - 1;
                            $windowStart = $lastPage - 4;
                        }

                        for ($i = $windowStart; $i <= $windowEnd; $i++) {
                            $pages[] = $i;
                        }

                        $pages = array_values(array_unique($pages));
                        sort($pages);
                    }
                @endphp
                {{-- Previous --}}
                <button wire:click="previousPage" @disabled($paginator->onFirstPage())
                    class="px-3 py-2 text-sm border dark:border-gray-700 rounded disabled:bg-gray-200 dark:disabled:bg-gray-800
                    {{ $paginator->onFirstPage() ? 'text-gray-400 dark:text-gray-500' : 'hover:bg-brand-100 dark:hover:bg-gray-800' }}">
                    <x-lucide-chevron-left class="w-4 h-4 text-gray-700 dark:text-gray-200" />
                </button>

                {{-- Page numbers --}}
                @foreach ($pages as $index => $page)
                    @if ($index > 0 && $page - $pages[$index - 1] > 1)
                        <span class="px-2 text-gray-500 dark:text-gray-400">…</span>
                    @endif
                    <button wire:click="gotoPage({{ $page }})"
                        class="px-3 py-2 text-sm border dark:border-gray-700 rounded
                        {{ $page === $paginator->currentPage() ? 'bg-brand-500 text-white' : 'hover:bg-brand-100 dark:hover:bg-gray-800 dark:text-gray-200' }}">
                        {{ $page }}
                    </button>
                @endforeach

                {{-- Next --}}
                <button wire:click="nextPage" @disabled(!$paginator->hasMorePages())
                    class="px-3 py-2.5 text-sm border dark:border-gray-700 rounded disabled:bg-gray-200 dark:disabled:bg-gray-800
                    {{ !$paginator->hasMorePages() ? 'text-gray-400 dark:text-gray-500' : 'hover:bg-brand-100 dark:hover:bg-gray-800' }}">
                    <x-lucide-chevron-right class="w-4 h-4 text-gray-700 dark:text-gray-200" />
                </button>
            </nav>


            <!-- Per page selector -->
        </div>
    @endif
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600 dark:text-gray-300">Per page</span>
        <select wire:model.change="perPage"
            class="px-3 py-2 border rounded-lg text-sm bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 dark:focus:ring-gray-600">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
        </select>
    </div>
</div>
