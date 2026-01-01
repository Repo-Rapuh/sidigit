<div x-data="{ showFilters: false }" class="space-y-3">
    <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-2">
        <div class="flex items-center gap-2">
            <div class="relative w-full">
                <x-lucide-search
                    class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Search..."
                    class="px-3 py-2 pl-9 border rounded-lg text-sm w-full bg-white text-gray-700 placeholder:text-gray-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:border-gray-700 dark:focus:border-brand-400 dark:focus:ring-brand-400/20" />
            </div>
        </div>

        <div class="flex items-center gap-2 sm:justify-self-end">
            {{-- Table actions --}}
            @foreach ($tableActions as $action)
                <button wire:click="{{ $action['method'] }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded {{ $action['class'] ?? 'bg-gray-800 hover:bg-gray-900 text-white' }} text-sm">
                    @if (!empty($action['icon']))
                        <x-dynamic-component :component="'lucide-' . $action['icon']" class="w-4 h-4" />
                    @endif
                    {{ $action['label'] }}
                </button>
            @endforeach

            @if (!empty($filtersView))
                <button type="button" @click="showFilters = !showFilters"
                    class="inline-flex items-center gap-2 rounded border border-brand-500 px-3 py-2 text-sm text-brand-600  hover:bg-brand-100  dark:text-brand-400 dark:border-brand-400 dark:hover:bg-gray-800"
                    :class="showFilters ? 'bg-brand-100 dark:bg-gray-800' : 'bg-white dark:bg-gray-900'">
                    <x-lucide-funnel-plus x-show="!showFilters" class="w-4 h-4" />
                    <x-lucide-funnel-x x-show="showFilters" class="w-4 h-4" />
                    <span>Filter</span>
                </button>
            @endif

            {{-- Bulk actions dropdown --}}
            @if ($this->selectionColumnCheckbox() && count($bulkActions) > 0 && count($selected) > 0)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="btn btn-info">Bulk</button>
                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 bg-white rounded shadow p-2 w-48 z-10 dark:bg-gray-900 dark:ring-1 dark:ring-gray-800">
                        @foreach ($bulkActions as $key => $b)
                            <button wire:click="{{ $b['method'] }}"
                                class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-800">
                                {{ $b['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (!empty($filtersView))
        <div x-show="showFilters" x-cloak class="w-full">
            @include($filtersView)
        </div>
    @endif
</div>
