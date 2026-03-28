{{--
    Custom styled select dropdown — matches zinc design language.

    Props:
      - wireModel   : Livewire property name to call $wire.set() on (string)
      - value       : currently selected value (string)
      - placeholder : label shown when nothing selected (default "Select…")
      - options     : array of ['value'=>'', 'label'=>'', 'color'=>'#hex'] items
      - align       : dropdown alignment — "left" | "right" (default "left")
      - id          : wrapper id (optional)
--}}

@props([
    'wireModel' => null,
    'value' => '',
    'placeholder' => 'Select…',
    'options' => [],
    'align' => 'left',
    'id' => null,
])

<div x-data="{
    open: false,
    selected: @js($value),
    options: @js($options),
    get label() {
        const opt = this.options.find(o => o.value === this.selected);
        return opt ? opt.label : null;
    },
    get color() {
        const opt = this.options.find(o => o.value === this.selected);
        return opt ? (opt.color ?? null) : null;
    },
    pick(val) {
        this.selected = val;
        this.open = false;
        @if($wireModel)
        $wire.set('{{ $wireModel }}', val);
        @endif
    },
}" @keydown.escape.window="open = false" class="inline-block relative" @if ($id)
    id="{{ $id }}"
    @endif
    >
    {{-- ── Trigger ── --}}
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-2 bg-surface hover:bg-surface-2 px-3 py-1.5 border border-surface hover:border-surface-2 focus:border-zinc-600 rounded-lg focus:outline-none text-fg text-sm whitespace-nowrap transition select-none"
        :class="open ? 'border-zinc-600 bg-surface-2' : ''" {{ $attributes }}>
        {{-- colour swatch (visible when option has color) --}}
        <span x-show="!!color" class="rounded-sm w-2 h-2 shrink-0"
            :style="color ? `background-color:${color}` : ''"></span>

        {{-- label --}}
        <span :class="selected ? 'text-fg' : 'text-fg-muted'" x-text="label ?? '{{ $placeholder }}'"></span>

        {{-- chevron --}}
        <svg class="w-3 h-3 text-zinc-600 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': open }"
            viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M2 4l4 4 4-4" />
        </svg>
    </button>

    {{-- ── Dropdown ── --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1" @click.outside="open = false"
        class="absolute top-full mt-1.5 z-50 min-w-[160px] bg-surface-4 border border-surface rounded-xl shadow-2xl shadow-black/60 py-1 origin-top-{{ $align }}"
        :class="'{{ $align }}'
        === 'right' ? 'right-0' : 'left-0'" style="display:none">
        {{-- "All" / clear option --}}
        <button type="button" @click="pick('')" class="flex items-center gap-2.5 px-3 py-2 w-full text-sm transition"
            :class="selected === '' ? 'text-fg bg-surface-2' : 'text-fg-muted hover:text-fg hover:bg-surface-2'">
            <span class="flex justify-center items-center w-2 h-2 shrink-0">
                <span x-show="selected === ''" class="bg-indigo-500 rounded-full w-1.5 h-1.5"></span>
            </span>
            <span>{{ $placeholder }}</span>
        </button>

        <div class="my-1 border-surface/60 border-t"></div>

        {{-- Option rows --}}
        @foreach ($options as $opt)
            <button type="button" @click="pick('{{ $opt['value'] }}')"
                class="flex items-center gap-2.5 px-3 py-2 w-full text-sm transition"
                :class="selected === '{{ $opt['value'] }}' ? 'text-fg bg-surface-2' :
                    'text-fg-muted hover:text-fg hover:bg-surface-2'">
                @if (!empty($opt['color']))
                    {{-- colour swatch --}}
                    <span class="rounded-sm w-2 h-2 shrink-0" style="background-color: {{ $opt['color'] }}"></span>
                @else
                    {{-- alignment spacer --}}
                    <span class="w-2 h-2 shrink-0"></span>
                @endif
                <span>{{ $opt['label'] }}</span>
                {{-- tick for active --}}
                <svg x-show="selected === '{{ $opt['value'] }}'" class="ml-auto w-3 h-3 text-indigo-400 shrink-0"
                    viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M2 6l3 3 5-5" />
                </svg>
            </button>
        @endforeach
    </div>
</div>
