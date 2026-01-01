@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'multiple' => false,
    'rules' => [],
    'key' => null,
])

@php
    $isRequired = $required || $attributes->has('required');
    $wireModel = $attributes->wire('model')->value();
    if (! $wireModel) {
        throw new Exception('You must wire:model to the dropzone input.');
    }
    $dropzoneRules = $rules;
    if ($isRequired) {
        $dropzoneRules = array_merge(['required'], $dropzoneRules);
    }
    $dropzoneKey = $key ?: 'dropzone-'.md5($wireModel);
@endphp

<div class="flex flex-col">
    @if ($label)
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ $label }}
            @if ($isRequired)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    <livewire:dropzone
        wire:model="{{ $wireModel }}"
        :rules="$dropzoneRules"
        :multiple="$multiple"
        :key="$dropzoneKey"
    />

    @if ($name)
        @error($name)
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    @endif
</div>
