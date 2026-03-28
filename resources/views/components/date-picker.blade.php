{{--
    Dark date-picker component — matches zinc design language.

    Props:
      - wireModel   : wire:model property name (string, e.g. "date")
      - value       : current value as YYYY-MM-DD string
      - placeholder : placeholder text (default "Pick a date")
      - id          : input id (optional)
      - align       : dropdown alignment — "left" | "right" (default "left")
--}}

@props([
    'wireModel' => null,
    'value' => '',
    'placeholder' => 'Pick a date',
    'id' => null,
    'align' => 'left',
])

<div x-data="{
    open: false,
    raw: @js($value),
    {{-- YYYY-MM-DD --}}
    viewYear: null,
    viewMonth: null,
    today: null,

    /* ── init ── */
    init() {
        const d = this.raw ? new Date(this.raw + 'T00:00:00') : new Date();
        this.viewYear = d.getFullYear();
        this.viewMonth = d.getMonth();
        const t = new Date();
        this.today = this.ymd(t.getFullYear(), t.getMonth(), t.getDate());
    },

    /* ── helpers ── */
    ymd(y, m, d) {
        return `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    },
    get displayValue() {
        if (!this.raw) return '';
        const [y, m, d] = this.raw.split('-').map(Number);
        const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${d} ${names[m-1]} ${y}`;
    },
    get monthLabel() {
        const names = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        return `${names[this.viewMonth]} ${this.viewYear}`;
    },
    prevMonth() {
        if (this.viewMonth === 0) {
            this.viewMonth = 11;
            this.viewYear--;
        } else this.viewMonth--;
    },
    nextMonth() {
        if (this.viewMonth === 11) {
            this.viewMonth = 0;
            this.viewYear++;
        } else this.viewMonth++;
    },
    goToToday() {
        const t = new Date();
        this.viewYear = t.getFullYear();
        this.viewMonth = t.getMonth();
    },
    get days() {
        const y = this.viewYear,
            m = this.viewMonth;
        const firstDay = new Date(y, m, 1).getDay();
        {{-- 0=Sun --}}
        const daysInMonth = new Date(y, m + 1, 0).getDate();
        const prev = new Date(y, m, 0).getDate();
        const cells = [];
        {{-- leading cells from previous month --}}
        for (let i = firstDay - 1; i >= 0; i--)
            cells.push({ day: prev - i, cur: false, ymd: this.ymd(m === 0 ? y - 1 : y, m === 0 ? 11 : m - 1, prev - i) });
        {{-- current month --}}
        for (let d = 1; d <= daysInMonth; d++)
            cells.push({ day: d, cur: true, ymd: this.ymd(y, m, d) });
        {{-- trailing cells to fill a 6-row grid (42 cells) --}}
        let trailing = 1;
        while (cells.length < 42)
            cells.push({ day: trailing++, cur: false, ymd: this.ymd(m === 11 ? y + 1 : y, m === 11 ? 0 : m + 1, trailing - 1) });
        return cells;
    },
    select(cell) {
        this.raw = cell.ymd;
        this.open = false;
        @if($wireModel)
        $wire.set('{{ $wireModel }}', cell.ymd);
        @endif
    },
}" x-init="init()" @keydown.escape.window="open = false" class="inline-block relative"
    @if ($id)
    id="{{ $id }}"
    @endif
    >
    {{-- ── Trigger button ── --}}
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-2 bg-surface hover:bg-surface-2 px-3 py-1.5 border border-surface hover:border-surface-2 focus:border-zinc-600 rounded-lg focus:outline-none text-fg text-sm transition select-none"
        :class="{ 'border-zinc-600 bg-surface-2': open }" {{ $attributes }}>
        {{-- Calendar icon --}}
        <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" viewBox="0 0 16 16" fill="none" stroke="currentColor"
            stroke-width="1.5">
            <rect x="1.5" y="2.5" width="13" height="12" rx="1.5" />
            <path d="M5 1v3M11 1v3M1.5 6.5h13" />
        </svg>
        <span class="font-medium" :class="raw ? 'text-fg' : 'text-fg-muted'">
            <span x-text="displayValue || '{{ $placeholder }}'"></span>
        </span>
        {{-- chevron --}}
        <svg class="w-3 h-3 text-zinc-600 transition-transform shrink-0" :class="{ 'rotate-180': open }"
            viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M2 4l4 4 4-4" />
        </svg>
    </button>

    {{-- ── Dropdown calendar ── --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1" @click.outside="open = false"
        class="absolute top-full mt-1.5 z-50 w-[268px] bg-surface-4 border border-surface rounded-xl shadow-2xl shadow-black/60 p-3 origin-top-{{ $align }}"
        :class="{
            'right-0': '{{ $align }}'
            === 'right',
            'left-0': '{{ $align }}'
            !== 'right'
        }"
        style="display:none">
        {{-- Navigation header --}}
        <div class="flex justify-between items-center mb-3 px-0.5">
            <button type="button" @click="prevMonth()"
                class="flex justify-center items-center hover:bg-surface-2 rounded-lg w-7 h-7 text-zinc-400 hover:text-fg transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 2L4 6l4 4" />
                </svg>
            </button>

            <span class="font-semibold text-fg-2 text-sm select-none" x-text="monthLabel"></span>

            <button type="button" @click="nextMonth()"
                class="flex justify-center items-center hover:bg-surface-2 rounded-lg w-7 h-7 text-zinc-400 hover:text-fg transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 2l4 4-4 4" />
                </svg>
            </button>
        </div>

        {{-- Day-of-week headers --}}
        <div class="grid grid-cols-7 mb-1">
            <template x-for="hdr in ['Su','Mo','Tu','We','Th','Fr','Sa']">
                <div class="flex justify-center items-center h-7 font-semibold text-[10px] text-zinc-600 uppercase tracking-wider select-none"
                    x-text="hdr"></div>
            </template>
        </div>

        {{-- Day grid --}}
        <div class="gap-y-0.5 grid grid-cols-7">
            <template x-for="cell in days" :key="cell.ymd">
                <button type="button" @click="select(cell)"
                    class="flex justify-center items-center rounded-lg h-8 font-medium text-sm transition select-none"
                    :class="{
                        'text-fg-muted hover:text-fg hover:bg-surface': !cell.cur,
                        'text-fg-3 hover:bg-surface-2 hover:text-fg': cell.cur && cell.ymd !== raw && cell.ymd !==
                            today,
                        'bg-surface-2 text-fg ring-1 ring-surface-2': cell.cur && cell.ymd === today && cell.ymd !==
                            raw,
                        'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40 ring-1 ring-indigo-500 scale-105': cell
                            .ymd === raw,
                        'ring-1 ring-indigo-500/50': cell.ymd === today && cell.ymd === raw,
                    }"
                    x-text="cell.day"></button>
            </template>
        </div>

        {{-- Footer --}}
        <div class="flex justify-between items-center mt-3 pt-2.5 border-surface/70 border-t">
            <button type="button" @click="goToToday()"
                class="font-medium text-[11px] text-zinc-500 hover:text-zinc-300 transition">
                Go to today
            </button>
            <button type="button"
                @click="raw = ''; @if ($wireModel) $wire.set('{{ $wireModel }}', ''); @endif"
                class="font-medium text-[11px] text-zinc-600 hover:text-zinc-400 transition">
                Clear
            </button>
        </div>
    </div>
</div>
