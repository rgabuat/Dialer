@props([
    'text' => null,
    'variant' => 'primary', // primary | secondary | warning | danger | ghost
    'size' => 'md',         // sm | md | lg
    'type' => 'button',
    'id' => null,
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2
             rounded-md font-medium transition
             focus:outline-none focus:ring-2 focus:ring-offset-2
             disabled:opacity-50 disabled:pointer-events-none';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-3 text-base',
    ];

    $variants = [
        'primary'   => 'bg-gradient-to-r from-fuchsia-500 to-purple-600 text-white hover:bg-purple-700 focus:ring-purple-500 w-full cursor-pointer',
        'secondary' => 'bg-gray-100 text-gray-900 hover:bg-gray-200 focus:ring-gray-400',
        'warning'   => 'bg-yellow-500 text-black hover:bg-yellow-600 focus:ring-yellow-400',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'ghost'     => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-300',
    ];

    $variantClasses = $variants[$variant] ?? $variants['primary'];
@endphp

<button
    type="{{ $type }}"
    @if($id) id="{{ $id }}" @endif
    @if($disabled) disabled @endif
    {{ $attributes->merge([
        'class' => $base.' '.$sizes[$size].' '.$variantClasses,
    ]) }}
>
    {{ $text ?? $slot }}
</button>