@props([
    'title' => null,
    'description' => null,
])

<div
    {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900']) }}>
    @if ($title)
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ $title }}
        </h2>
    @endif

    @if ($description)
        <p class="mt-1 mb-8 text-sm text-gray-500 dark:text-gray-400">
            {{ $description }}
        </p>
    @endif

    {{ $slot }}
</div>
