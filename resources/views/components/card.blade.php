@props([
    'header' => null,
    'footer' => null,
])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-xl border border-gray-200 shadow-sm'
]) }}>

    {{-- Header --}}
    @if($header)
        <div class="px-6 py-4 border-b border-gray-200">
            {{ $header }}
        </div>
    @endif

    {{-- Body --}}

    {{ $slot }}

    {{-- Footer --}}
    @if($footer)
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
            {{ $footer }}
        </div>
    @endif
</div>
