@props([
    'src' => null,
    'size' => 'h-14',
    'alt' => 'Brand logo',
])

@php
    $logo = $src ?? asset('images/logo/logo.png');
@endphp

<div class="flex justify-center m-auto">
    <!-- <img
        src="{{ $logo }}"
        alt="{{ $alt }}"
        class="{{ $size }} object-contain"
    /> -->
    <h1 class="text-2xl font-semibold tracking-tight text-white flex items-center">
        {{ config('app.name') }}
    </h1>
</div>
