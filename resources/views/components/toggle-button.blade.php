@props([
    'checked' => false,
    'label' => null,
    'size' => 'md',      // sm | md | lg
    'id' => null,
    'name' => null,
])

@php
    $sizes = [
        'sm' => ['track' => 'w-8 h-4', 'thumb' => 'w-3 h-3', 'translate' => 'translate-x-4'],
        'md' => ['track' => 'w-10 h-6', 'thumb' => 'w-5 h-5', 'translate' => 'translate-x-4'],
        'lg' => ['track' => 'w-12 h-7', 'thumb' => 'w-6 h-6', 'translate' => 'translate-x-5'],
    ];

    $s = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    x-data="{ on: {{ $checked ? 'true' : 'false' }} }"
    role="switch"
    :aria-checked="on.toString()"
    @click="on = !on"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-3 cursor-pointer select-none',
    ]) }}
>
    {{-- Hidden input (for forms) --}}
    @if($name)
        <input
            type="hidden"
            @if($id) id="{{ $id }}" @endif
            name="{{ $name }}"
            :value="on ? 1 : 0"
        >
    @endif

    <!-- Track -->
    <div
        class="relative rounded-full transition-colors duration-200 bg-fuchsia-600 {{ $s['track'] }}"
        :class="on ? 'bg-gradient-to-r from-fuchsia-500 to-purple-600' : 'bg-gray-600'"
    >
        <!-- Thumb -->
        <span
            class="absolute top-0.5 left-0.5 rounded-full bg-white
                   transition-transform duration-200 {{ $s['thumb'] }}"
            :class="on ? '{{ $s['translate'] }}' : ''"
        ></span>
    </div>

    <!-- Label -->
    @if($label)
        <label
            @if($id) for="{{ $id }}" @endif
            class="text-sm text-neutral-400"
        >
            {{ $label }}
        </label>
    @endif
</div>
