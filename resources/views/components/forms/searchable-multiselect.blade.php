@props([
    'label' => null,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'placeholder' => 'Pilih...',
    'required' => false,
])

<div x-data="{
    open: false,
    search: '',
    top: 0,
    left: 0,
    width: 0,
    value: @entangle($attributes->wire('model')),
    get filtered() {
        const term = this.search.toLowerCase();
        return this.options.filter(o => String(o.label).toLowerCase().includes(term));
    },
    options: @js(
        collect($options)->map(fn ($opt) => [
            'value' => data_get($opt, $optionValue),
            'label' => data_get($opt, $optionLabel),
        ])->values()
    ),
    labelFor(val) {
        const found = this.options.find(o => String(o.value) === String(val));
        return found ? found.label : '';
    },
    isSelected(val) {
        if (!Array.isArray(this.value)) return false;
        return this.value.some(v => String(v) === String(val));
    },
    toggle(val) {
        if (!Array.isArray(this.value)) {
            this.value = [];
        }
        if (this.isSelected(val)) {
            this.value = this.value.filter(v => String(v) !== String(val));
        } else {
            this.value = [...this.value, val];
        }
    },
    updatePosition() {
        if (!this.$refs.trigger) return;
        const rect = this.$refs.trigger.getBoundingClientRect();
        this.top = rect.bottom + window.scrollY;
        this.left = rect.left + window.scrollX;
        this.width = rect.width;
    },
}" x-init="
    if (!Array.isArray(value)) { value = []; }
    const onScroll = () => { if (open) updatePosition(); };
    window.addEventListener('scroll', onScroll, true);
    window.addEventListener('resize', onScroll);
" class="relative">
    @if ($label)
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ $label }}
            @if ($required || $attributes->has('required'))
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <button type="button" x-ref="trigger"
        @click="open = !open; if (open) { $nextTick(() => updatePosition()); }"
        class="form-input mt-2 flex items-center justify-between gap-2 text-left">
        <span x-text="value.length ? `${value.length} dipilih` : '{{ $placeholder }}'"
            :class="value.length ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500'"></span>
        <span class="shrink-0 text-gray-500 dark:text-gray-400">
            <x-lucide-chevron-down x-show="!open" class="h-4 w-4" />
            <x-lucide-chevron-up x-show="open" class="h-4 w-4" />
        </span>
    </button>

    <template x-if="value.length">
        <div class="mt-2 flex flex-wrap gap-2">
            <template x-for="val in value" :key="val">
                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    <span x-text="labelFor(val)"></span>
                    <button type="button" @click.stop="toggle(val)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </span>
            </template>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="open" x-cloak
            @click.outside="open = false"
            @keydown.escape.window="open = false"
            :style="`position: absolute; top: ${top}px; left: ${left}px; width: ${width}px;`"
            class="z-50 mt-2 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
            <div class="p-2">
                <input type="text" x-model="search" placeholder="Cari..." class="form-input" />
            </div>
            <ul class="max-h-56 overflow-auto py-1">
                <template x-for="opt in filtered" :key="opt.value">
                    <li>
                        <button type="button" @click="toggle(opt.value)"
                            class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-800"
                            :class="isSelected(opt.value) ? 'bg-brand-50 text-brand-700 dark:bg-gray-800 dark:text-brand-300' : ''">
                            <span x-text="opt.label"></span>
                        </button>
                    </li>
                </template>
                <template x-if="filtered.length === 0">
                    <li class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</li>
                </template>
            </ul>
        </div>
    </template>
</div>
