@php
    $optionsJson = collect($options)->map(function ($option) use ($optionLabelKey, $optionValueKey) {
        return [
            'label' => $option[$optionLabelKey] ?? '',
            'value' => $option[$optionValueKey] ?? null,
        ];
    })->values();
@endphp

<div x-data="{
        open: false,
        query: '',
        options: @js($optionsJson),
        placeholder: @js($placeholder),
        searchPlaceholder: @js($searchPlaceholder),
        emptyText: @js($emptyText),
        allowClear: @js($allowClear),
        value: @entangle('value').live,
        get selected() {
            if (this.value === null || this.value === undefined) {
                return null;
            }
            return this.options.find(option => this.isSame(option.value, this.value)) ?? null;
        },
        get selectedLabel() {
            return this.selected ? this.selected.label : null;
        },
        get filtered() {
            if (this.query.trim() === '') {
                return this.options;
            }
            const term = this.query.trim().toLowerCase();
            return this.options.filter(option => (option.label ?? '').toLowerCase().includes(term));
        },
        isSame(a, b) {
            if (a === null || b === null) return a === b;
            return String(a) === String(b);
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.search?.focus());
            }
        },
        select(option) {
            this.value = option.value;
            this.open = false;
        },
        clear() {
            this.value = null;
            this.query = '';
            this.open = false;
        }
    }"
    @click.outside="open = false" class="relative" x-cloak>
    <button type="button" @click="toggle"
        class="flex w-full items-center justify-between gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-left text-sm text-gray-900 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        <span x-text="selectedLabel ?? placeholder"></span>
        <span class="flex items-center gap-1">
            <template x-if="allowClear && value !== null && value !== undefined">
                <span role="button" tabindex="-1" @click.stop="clear"
                    class="rounded-md border border-transparent p-1 text-gray-400 transition hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 5 10 10M15 5L5 15" />
                    </svg>
                </span>
            </template>
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 8 4 4 4-4" />
            </svg>
        </span>
    </button>

    <div x-show="open" x-transition
        class="absolute z-50 mt-2 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-3 py-2 dark:border-gray-800">
            <input type="text" x-ref="search" x-model.debounce.200ms="query" :placeholder="searchPlaceholder"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                autofocus>
        </div>
        <ul class="max-h-60 divide-y divide-gray-100 overflow-y-auto dark:divide-gray-800">
            <template x-if="filtered.length === 0">
                <li class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" x-text="emptyText"></li>
            </template>
            <template x-for="option in filtered" :key="option.value">
                <li>
                    <button type="button" @click="select(option)"
                        class="flex w-full items-center justify-between px-4 py-2 text-left text-sm text-gray-700 transition hover:bg-brand-50 hover:text-brand-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        <span x-text="option.label"></span>
                        <svg x-show="isSame(option.value, value)" class="h-4 w-4 text-brand-500" viewBox="0 0 20 20"
                            fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m5 10 3 3 7-7" />
                        </svg>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>
