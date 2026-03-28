{{--
    Custom styled select dropdown — matches zinc design language.

    Props:
      - wireModel   : Livewire property name to call $wire.set() on (string)
      - value       : currently selected value — string for single, array for multiple
      - placeholder : label shown when nothing selected (default "Select…")
      - options     : array of ['value'=>'', 'label'=>'', 'color'=>'#hex'] items
      - align       : dropdown alignment — "left" | "right" (default "left")
      - id          : wrapper id (optional)
      - multiple    : allow picking multiple values; backing Livewire property must be array (default false)
--}}

@props([
    'wireModel' => null,
    'value' => '',
    'placeholder' => 'Select…',
    'options' => [],
    'align' => 'left',
    'id' => null,
    'multiple' => false,
])

<div x-data="{
    open: false,
    multiple: @js($multiple),
    selected: @js($multiple ? (is_array($value) ? $value : ($value ? [$value] : [])) : $value),
    options: @js($options),
    get isEmpty() {
        return this.multiple ? this.selected.length === 0 : this.selected === '';
    },
    get label() {
        if (this.multiple) {
            if (this.selected.length === 0) return null;
            if (this.selected.length === 1) {
                const opt = this.options.find(o => o.value === this.selected[0]);
                return opt ? opt.label : null;
            }
            return this.selected.length + ' selected';
        }
        const opt = this.options.find(o => o.value === this.selected);
        return opt ? opt.label : null;
    },
    get color() {
        if (this.multiple) {
            if (this.selected.length !== 1) return null;
            const opt = this.options.find(o => o.value === this.selected[0]);
            return opt ? (opt.color ?? null) : null;
        }
        const opt = this.options.find(o => o.value === this.selected);
        return opt ? (opt.color ?? null) : null;
    },
    isActive(val) {
        return this.multiple ? this.selected.includes(val) : this.selected === val;
    },
    pick(val) {
        if (this.multiple) {
            if (this.selected.includes(val)) {
                this.selected = this.selected.filter(v => v !== val);
            } else {
                this.selected = [...this.selected, val];
            }
            @if($wireModel)
            $wire.set('{{ $wireModel }}', this.selected);
            @endif
        } else {
            this.selected = val;
            this.open = false;
            @if($wireModel)
            $wire.set('{{ $wireModel }}', val);
            @endif
        }
    },
    clear() {
        this.selected = this.multiple ? [] : '';
        this.open = false;
        @if($wireModel)
        $wire.set('{{ $wireModel }}', this.selected);
        @endif
    },
    init() {
        @if($wireModel)
        $wire.$watch('{{ $wireModel }}', val => {
            if (val === undefined) return;
            if (this.multiple) {
                if (Array.isArray(val) && JSON.stringify(val) !== JSON.stringify(this.selected)) {
                    this.selected = val;
                }
            } else {
                if (val !== this.selected) this.selected = val;
            }
        });
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

        {{-- colour swatch — always in DOM to avoid width shift; invisible when no color --}}
        <span class="rounded-sm w-2 h-2 transition-opacity duration-100 shrink-0"
            :class="color ? 'opacity-100' : 'opacity-0'" :style="color ? `background-color:${color}` : ''"></span>

        {{-- placeholder text is always the fixed anchor — never replaced —
             so the button width never changes when selections change --}}
        <span :class="isEmpty ? 'text-fg-muted' : 'text-fg'" class="whitespace-nowrap">{{ $placeholder }}</span>

        {{-- count badge — always rendered (locks button width), opacity toggled --}}
        <span
            class="inline-flex justify-center items-center bg-indigo-500/20 px-1.5 py-0.5 rounded min-w-[20px] font-bold tabular-nums text-[11px] text-indigo-400 transition-opacity duration-100"
            :class="isEmpty ? 'opacity-0' : 'opacity-100'" x-text="multiple ? selected.length : '1'"></span>

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
        class="absolute top-full mt-1.5 z-50 min-w-[160px] bg-surface-4 border border-surface rounded-xl shadow-2xl shadow-black/60 py-1 overflow-hidden origin-top-{{ $align }}"
        :class="'{{ $align }}'
        === 'right' ? 'right-0' : 'left-0'" style="display:none">
        {{-- "All" / clear option --}}
        <button type="button" @click="clear()" class="flex items-center gap-2.5 px-3 py-2 w-full text-sm transition"
            :class="isEmpty ? 'text-fg bg-surface-2' : 'text-fg-muted hover:text-fg hover:bg-surface-2'">
            <span class="flex justify-center items-center w-2 h-2 shrink-0">
                <span x-show="isEmpty" class="bg-indigo-500 rounded-full w-1.5 h-1.5"></span>
            </span>
            <span>{{ $placeholder }}</span>
        </button>

        <div class="my-1 border-surface/60 border-t"></div>

        {{-- Option rows — rendered via x-for so active state stays in sync with Alpine's reactive `selected` --}}
        <template x-for="opt in options" :key="opt.value">
            <button type="button" @click="pick(opt.value)"
                class="flex items-center gap-2.5 px-3 py-2 w-full text-sm transition"
                :class="isActive(opt.value) ? 'text-fg bg-surface-2' : 'text-fg-muted hover:text-fg hover:bg-surface-2'">
                {{-- colour swatch (shown when option has color) --}}
                <span x-show="!!opt.color" class="rounded-sm w-2 h-2 shrink-0"
                    :style="opt.color ? `background-color:${opt.color}` : ''"></span>
                {{-- alignment spacer (shown when no color) --}}
                <span x-show="!opt.color" class="w-2 h-2 shrink-0"></span>
                <span x-text="opt.label"></span>
                {{-- tick for active --}}
                <svg x-show="isActive(opt.value)" class="ml-auto w-3 h-3 text-indigo-400 shrink-0" viewBox="0 0 12 12"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M2 6l3 3 5-5" />
                </svg>
            </button>
        </template>
    </div>
</div>
