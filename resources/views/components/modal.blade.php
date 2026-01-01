@props([
    'show' => false,
    'maxWidth' => 'md', // sm | md | lg | xl
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        'full' => 'max-w-full',
        default => 'max-w-md',
    };
@endphp

<div x-data="{ open: @entangle($attributes->wire('model')) }" x-cloak>
    <!-- Overlay -->
    <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 z-[99999] bg-black/50 backdrop-blur-sm"
        @click="$wire.closeModal()"></div>

    <!-- Modal -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="fixed inset-0 z-[100000] flex items-center justify-center" @keydown.escape.window="$wire.closeModal()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full {{ $maxWidthClass }} mx-4 overflow-hidden">
            {{-- Header --}}
            @isset($header)
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                        {{ $header }}
                    </h2>
                    <button @click="$wire.closeModal()"
                        class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                        ✕
                    </button>
                </div>
            @endisset

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4 text-gray-700 dark:text-gray-200">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div
                    class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-end gap-2">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
