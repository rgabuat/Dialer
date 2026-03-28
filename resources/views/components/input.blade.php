@props([
    'type' => 'text',
    'id' => null,
    'name' => null,
    'placeholder' => null,
    'disabled' => false,
    'readonly' => false,
    'error' => false,
])

@php
    $base = 'block w-full rounded-md bg-surface-2 border text-sm transition focus:outline-none py-2 px-3 text-fg';
    $normal = 'border-neutral-800 focus:border-indigo-500 focus:ring-indigo-500';
    $errorClasses = 'border-red-500 focus:border-red-500 focus:ring-red-500';
    $disabledClasses = 'bg-gray-100 text-gray-500 cursor-not-allowed';
@endphp

<input type="{{ $type }}" @if ($id) id="{{ $id }}" @endif
    @if ($name) name="{{ $name }}" @endif
    @if ($placeholder) placeholder="{{ $placeholder }}" @endif
    @if ($disabled) disabled @endif @if ($readonly) readonly @endif
    {{ $attributes->merge([
        'class' => $base . ' ' . ($error ? $errorClasses : $normal) . ' ' . ($disabled ? $disabledClasses : ''),
    ]) }} />
