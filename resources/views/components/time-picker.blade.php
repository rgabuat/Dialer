{{--
    Custom time-picker component — styled to match the app design system.

    Props:
      - wireModel : Livewire property name (string), e.g. "activityStart"
      - value     : current value in HH:MM (24-h) format, e.g. "09:30"
      - id        : optional id for the hidden input
--}}
@props([
    'wireModel' => '',
    'value' => '',
    'id' => null,
])

@php
    // Parse the incoming HH:MM value into parts for Alpine.js initialization
    [$initH, $initM] = strlen($value) === 5 ? [(int) substr($value, 0, 2), (int) substr($value, 3, 2)] : [9, 0];

    $initAmPm = $initH >= 12 ? 'pm' : 'am';
    $initH12 = $initH % 12 === 0 ? 12 : $initH % 12;
@endphp

<div x-data="{
    h: {{ $initH12 }},
    m: {{ $initM }},
    ampm: '{{ $initAmPm }}',
    get hh24() {
        let h = this.h % 12;
        if (this.ampm === 'pm') h += 12;
        return String(h).padStart(2, '0') + ':' + String(this.m).padStart(2, '0');
    },
    sync() { $refs.hidden.value = this.hh24;
        $refs.hidden.dispatchEvent(new Event('input')); }
}" x-init="sync()" class="flex items-center gap-1">

    {{-- Hidden input that Livewire actually binds to --}}
    <input type="hidden" x-ref="hidden" @if ($wireModel) wire:model.live="{{ $wireModel }}" @endif
        @if ($id) id="{{ $id }}" @endif :value="hh24" />

    {{-- Hour --}}
    <select x-model.number="h" @change="sync()"
        class="bg-surface-2 px-2 py-2 border border-neutral-800 focus:border-indigo-500 rounded-md focus:outline-none w-14 font-mono text-fg text-sm text-center transition appearance-none cursor-pointer">
        @for ($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
        @endfor
    </select>

    <span class="font-bold text-fg-muted select-none">:</span>

    {{-- Minute --}}
    <select x-model.number="m" @change="sync()"
        class="bg-surface-2 px-2 py-2 border border-neutral-800 focus:border-indigo-500 rounded-md focus:outline-none w-14 font-mono text-fg text-sm text-center transition appearance-none cursor-pointer">
        @foreach ([0, 15, 30, 45] as $min)
            <option value="{{ $min }}">{{ str_pad($min, 2, '0', STR_PAD_LEFT) }}</option>
        @endforeach
    </select>

    {{-- AM / PM toggle --}}
    <div class="flex border border-neutral-800 rounded-md overflow-hidden font-medium text-sm">
        <button type="button" @click="ampm = 'am'; sync()"
            :class="ampm === 'am'
                ?
                'bg-indigo-600 text-white' :
                'bg-surface-2 text-fg-muted hover:text-fg'"
            class="px-2.5 py-2 transition select-none">AM</button>
        <button type="button" @click="ampm = 'pm'; sync()"
            :class="ampm === 'pm'
                ?
                'bg-indigo-600 text-white' :
                'bg-surface-2 text-fg-muted hover:text-fg'"
            class="px-2.5 py-2 transition select-none">PM</button>
    </div>

</div>
