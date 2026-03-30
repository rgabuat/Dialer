@props([
    'value' => 0, // 0-100 percentage
    'color' => '#3b82f6',
    'size' => 52, // px
    'thickness' => 5,
])
@php
    $r = $size / 2 - $thickness;
    $circumference = round(2 * M_PI * $r, 2);
    $filled = round($circumference * ($value / 100), 2);
    $gap = $circumference - $filled;
@endphp

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}"
    class="-rotate-90 shrink-0">
    {{-- Track --}}
    <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="#27272a"
        stroke-width="{{ $thickness }}" />
    {{-- Fill (animates from 0 to target via stroke-dashoffset) --}}
    <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none"
        stroke="{{ $color }}" stroke-width="{{ $thickness }}" stroke-linecap="round"
        stroke-dasharray="{{ $circumference }}"
        style="--donut-start:{{ $circumference }}; --donut-offset:{{ round($circumference - $filled, 2) }}; stroke-dashoffset:{{ $circumference }}; animation:donut-fill 0.8s cubic-bezier(0.16,1,0.3,1) 0.2s forwards" />
</svg>
