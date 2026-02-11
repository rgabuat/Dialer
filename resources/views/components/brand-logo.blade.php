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
    DIALER
    <span class="ml-1 font-semibold">APP</span>
    <span
        class="ml-0.5 font-bold bg-gradient-to-r from-fuchsia-500 to-purple-600
               bg-clip-text text-transparent drop-shadow-sm"
    >
        .
    </span>
</h1>
</div>
