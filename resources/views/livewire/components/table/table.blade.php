{{-- Table --}}
<div class="bg-white rounded-xl shadow dark:bg-gray-900 dark:shadow-none dark:ring-1 dark:ring-gray-800">
    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">
            @php
                $selectionColumnCheckbox = $this->selectionColumnCheckbox();
            @endphp

            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    {{-- selection column --}}
                    <th class="px-4 text-left py-3">
                        @if ($selectionColumnCheckbox)
                            <input type="checkbox" wire:model.change="selectAll"
                                class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500 dark:focus:ring-brand-400/30" />
                        @else
                            <span class="text-gray-500 dark:text-gray-400">#</span>
                        @endif
                    </th>

                    {{-- columns --}}
                    @foreach ($columns as $column)
                        <th class="px-4 py-3 text-left">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900 dark:text-gray-200">{{ $column['label'] }}</span>
                                @if ($column['sortable'] ?? false)
                                    <button wire:click="sortBy('{{ $column['field'] }}')"
                                        class="text-xs text-gray-500 dark:text-gray-400">
                                        {{-- Simple indicator --}}
                                        @if ($sortField === $column['field'])
                                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                        @else
                                            ↕
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </th>
                    @endforeach

                    @if ($rowActions)
                        <th class="px-4 py-3 text-right"></th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr class="border-t hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/60"
                        wire:key="row-{{ $row->id }}">
                        <td class="px-4 py-3">
                            @if ($selectionColumnCheckbox)
                                <input type="checkbox" value="{{ $row->id }}" wire:model.change="selected"
                                    class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500 dark:focus:ring-brand-400/30" />
                            @else
                                <span class="text-gray-600 dark:text-gray-300">
                                    {{ ($rows->firstItem() ?? 0) + $loop->index }}
                                </span>
                            @endif
                        </td>

                        @foreach ($columns as $column)
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-200">
                                @if (isset($column['view']))
                                    @include($column['view'], ['row' => $row])
                                @elseif(isset($column['format']) && is_callable($column['format']))
                                    {{ $column['format']($row) ?? '-' }}
                                @else
                                    {{ data_get($row, $column['field']) ?? '-' }}
                                @endif
                            </td>
                        @endforeach

                        @if ($rowActions)
                            <td class="px-4 py-3 relative text-right" wire:key="action-{{ $row->id }}">
                                <div x-data="{
                                    open: false,
                                    style: '',
                                    toggle(el) {
                                        const rect = el.getBoundingClientRect();
                                        this.open = !this.open;
                                        if (!this.open) return;
                                        this.$nextTick(() => {
                                            const panel = this.$refs.panel;
                                            const dropdownHeight = panel ? panel.offsetHeight : 160;
                                            const dropdownWidth = panel ? panel.offsetWidth : 192;
                                            const spaceBelow = window.innerHeight - rect.bottom;
                                            const top = spaceBelow < dropdownHeight ?
                                                rect.top - dropdownHeight - 8 :
                                                rect.bottom + 8;
                                            const left = Math.min(
                                                Math.max(8, rect.right - dropdownWidth),
                                                window.innerWidth - dropdownWidth - 8
                                            );
                                            this.style = `top: ${top}px; left: ${left}px;`;
                                        });
                                    }
                                }" class="relative">
                                    <!-- Trigger -->
                                    <button x-ref="trigger" @click="toggle($el)"
                                        class="px-3 py-2 border rounded text-sm inline-flex items-center gap-2 dark:border-gray-700 dark:text-gray-100">
                                        <span>Action</span>
                                        <x-lucide-chevron-up x-show="open"
                                            class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                        <x-lucide-chevron-down x-show="!open"
                                            class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                    </button>

                                    <!-- Dropdown (teleported) -->
                                    <template x-teleport="body">
                                        <div x-show="open" x-transition @click.outside="open = false"
                                            class="fixed z-50 w-48 bg-white rounded-lg shadow-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800"
                                            x-ref="panel" :style="style">
                                            <ul class="py-1">
                                                @foreach ($rowActions as $action)
                                                    <li>
                                                        <button
                                                            wire:click="{{ $action['method'] }}({{ $row->id }})"
                                                            @click="open = false"
                                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex items-center gap-2 {{ $action['class'] ?? 'text-gray-700' }}">
                                                            @if (!empty($action['icon']))
                                                                <x-dynamic-component :component="'lucide-' . $action['icon']"
                                                                    class="w-4 h-4" />
                                                            @endif
                                                            {{ $action['label'] }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($rowActions ? 2 : 1) }}"
                            class="py-6 text-center text-gray-500">
                            No data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
