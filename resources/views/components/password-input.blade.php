@props([
    'id' => null,
    'name' => null,
    'placeholder' => '••••••••',
    'error' => false,
    'font' => 'sm', // xs | sm | base | lg
])

@php
    $fonts = [
        'xs'   => 'text-xs',
        'sm'   => 'text-sm',
        'base' => 'text-base',
        'lg'   => 'text-lg',
    ];

    $fontClass = $fonts[$font] ?? $fonts['sm'];
@endphp

<div
    class="relative"
    x-data="{ show: false }"
>
    <x-input
        :id="$id"
        :name="$name"
        :type="'password'"
        :placeholder="$placeholder"
        :error="$error"
        x-bind:type="show ? 'text' : 'password'"
        class="pr-10 {{ $fontClass }}"
        {{ $attributes }}
    />

    <!-- Toggle button -->
    <button
        type="button"
        tabindex="-1"
        class="absolute inset-y-0 right-0 flex items-center px-3
               text-gray-500 hover:text-gray-700
               cursor-pointer focus:outline-none"
        x-on:click="show = !show"
    >
        <span x-show="!show">
            <x-heroicon-o-eye class="w-5 h-5" />
        </span>

        <span x-show="show" x-cloak>
            <x-heroicon-o-eye-slash class="w-5 h-5" />
        </span>
    </button>
</div>
