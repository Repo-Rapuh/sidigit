@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'multiple' => false,
])

<x-forms.file-upload
    :label="$label"
    :name="$name"
    :required="$required"
    :multiple="$multiple"
    :rules="['image', 'mimes:jpg,jpeg,png', 'max:2048']"
    {{ $attributes }}
/>
