<div class="flex flex-col gap-4 p-6 h-full" x-data="{
    isToday: @js($isToday),
    _chan: null,
    init() {
        if (this.isToday) {
            this._chan = window.Echo.private('agent-status');
            this._chan.listen('.AgentStatusUpdated', (e) => {
                // Instant badge update via window event (no re-render needed)
                window.dispatchEvent(new CustomEvent('agent-row-update', { detail: e }));
                // Delay slightly so the DB write completes, then re-render timeline
                setTimeout(() => this.$wire.$refresh(), 800);
            });
        }
    },
    destroy() { if (this._chan) this._chan.stopListening('.AgentStatusUpdated'); }
}">

    {{-- ── Page header ──────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-3 shrink-0">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-bold text-fg text-xl">Shift Monitor</h1>
                @if ($isToday)
                    <span
                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2 py-0.5 rounded-full font-semibold text-[11px] text-accent-green">
                        <span class="bg-green-400 rounded-full w-1.5 h-1.5 animate-pulse"></span>
                        LIVE
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-fg-muted text-sm">Monitor scheduled slots and activity for day-to-day agents.</p>
        </div>
        <div class="text-right">
            <div class="font-semibold text-fg text-sm">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</div>
            <div class="flex justify-end items-center gap-2 mt-0.5">
                <span class="text-fg-muted text-xs">{{ $nowInRosterTz->format('g:i:s A') }}</span>
                <span
                    class="bg-surface-2 px-1.5 py-0.5 rounded font-mono text-[10px] text-fg-muted">{{ $rosterTz }}</span>
            </div>
            <div class="mt-0.5 text-fg-muted text-xs">{{ number_format($totalAgents) }} agents on shift</div>
        </div>
    </div>

    {{-- ── Controls ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search agents..."
            class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-48 text-fg text-sm transition placeholder-fg-muted">

        <x-date-picker wire-model="date" :value="$date" />

        <x-select-dropdown wire-model="filterStatus" :value="$filterStatus" placeholder="All Statuses" :multiple="true"
            :options="$statusTypes
                ->map(fn($t) => ['value' => $t->slug, 'label' => $t->name, 'color' => $t->color])
                ->values()
                ->all()" />

        <x-select-dropdown wire-model="filterGroup" :value="$filterGroup" placeholder="All Groups" :multiple="true"
            :options="$userGroups->map(fn($g) => ['value' => $g->name, 'label' => $g->name])->values()->all()" />

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-1.5 ml-1">
            @foreach ($statusTypes as $type)
                @php $color = $type->color ?? '#6366f1'; @endphp
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide"
                    style="background-color: {{ $color }}25; color: {{ $color }}">
                    <span class="rounded-full w-1.5 h-1.5 shrink-0"
                        style="background-color: {{ $color }}"></span>
                    {{ $type->name }}
                </span>
            @endforeach
        </div>
    </div>
    {{-- ── Stats strip ────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 text-xs shrink-0">
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="text-fg-muted">On Shift</span>
            <span class="font-semibold text-fg-2">{{ $totalAgents }}</span>
        </div>
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="bg-green-400 rounded-full w-1.5 h-1.5 animate-pulse shrink-0"></span>
            <span class="text-fg-muted">Available Now</span>
            <span class="font-semibold text-accent-green">{{ $availableNow }}</span>
        </div>
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="text-fg-muted">Avg Utilization</span>
            <span
                class="font-semibold {{ $avgUtil >= 70 ? 'text-accent-green' : ($avgUtil >= 40 ? 'text-accent-yellow' : 'text-accent-red') }}">{{ $avgUtil }}%</span>
        </div>
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="text-fg-muted">Available Time</span>
            <span
                class="font-semibold text-fg-2">{{ \App\Livewire\Activity\ShiftMonitoring::formatSeconds((int) $availSeconds) }}</span>
        </div>
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="text-fg-muted">Total Logged</span>
            <span
                class="font-semibold text-fg-2">{{ \App\Livewire\Activity\ShiftMonitoring::formatSeconds((int) $totalSeconds) }}</span>
        </div>
    </div>
    {{-- ── Gantt Card ───────────────────────────────────────────── --}}
    <div class="flex flex-col flex-1 bg-surface border border-surface rounded-xl min-h-0 transition-opacity duration-200 [overflow:clip]"
        wire:loading.class.delay="opacity-50">

        @if ($grouped->isEmpty())
            <div class="flex flex-1 justify-center items-center py-20 text-fg-muted text-sm italic">
                No shift data found for this date.
            </div>
        @else
            {{-- Scrollable Gantt body (both x + y) --}}
            <div class="flex-1 min-h-0 overflow-auto select-none no-scrollbar" style="cursor: grab;"
                style="cursor: grab;" x-data="{
                    dragging: false,
                    startX: 0,
                    startY: 0,
                    scrollLeft: 0,
                    scrollTop: 0,
                    _fn: null,
                    init() {
                        this._fn = () => {
                            this.$el.style.maxHeight = (window.innerHeight - this.$el.getBoundingClientRect().top - 24) + 'px';
                        };
                        this._fn();
                        window.addEventListener('resize', this._fn);
                    },
                    destroy() { window.removeEventListener('resize', this._fn); },
                    onDown(e) {
                        if (e.button !== 0) return;
                        this.dragging = true;
                        this.startX = e.pageX;
                        this.startY = e.pageY;
                        this.scrollLeft = $el.scrollLeft;
                        this.scrollTop = $el.scrollTop;
                        $el.style.cursor = 'grabbing';
                    },
                    onMove(e) {
                        if (!this.dragging) return;
                        e.preventDefault();
                        $el.scrollLeft = this.scrollLeft - (e.pageX - this.startX);
                        $el.scrollTop = this.scrollTop - (e.pageY - this.startY);
                    },
                    onUp() {
                        this.dragging = false;
                        $el.style.cursor = 'grab';
                    },
                }" @mousedown="onDown($event)" @mousemove="onMove($event)"
                @mouseup="onUp()" @mouseleave="onUp()">

                {{-- Min-width wrapper so horizontal scroll works --}}
                <div class="relative" style="min-width: {{ 260 + $timelineWidth }}px">

                    {{-- "Now" time indicator --}}
                    @if ($isToday)
                        <div x-data="{
                            left: -1,
                            _t: null,
                            update() {
                                const serverNowTs = {{ $nowTs }};
                                const clientOffsetSecs = Math.round(Date.now() / 1000) - serverNowTs;
                                const elapsed = (serverNowTs + clientOffsetSecs - {{ $visibleStartTs }}) / 60;
                                const px = Math.round(elapsed * {{ $pxPerMin }});
                                this.left = (px >= 0 && px <= {{ $timelineWidth }}) ? (260 + px) : -1;
                            },
                            init() {
                                this.update();
                                this._t = setInterval(() => this.update(), 30000);
                            },
                            destroy() { clearInterval(this._t); }
                        }" class="top-0 bottom-0 z-[9] absolute w-0 pointer-events-none"
                            :class="{ 'hidden': left < 0 }" :style="'left: ' + left + 'px'">
                            <div class="w-px h-full opacity-40"
                                style="background: linear-gradient(to bottom, #a1a1aa 0%, rgba(161,161,170,0.1) 100%)">
                            </div>
                            <div class="top-9 absolute bg-zinc-500 rounded-full w-1.5 h-1.5 -translate-x-[2px]">
                            </div>
                        </div>
                    @endif

                    {{-- ── Sticky header row ──────────────────────────── --}}
                    <div class="top-0 z-20 sticky flex items-stretch bg-surface border-b"
                        style="border-color: var(--border)">
                        {{-- Corner cell (sticky left + top) --}}
                        <div
                            class="left-0 z-30 sticky flex items-center bg-surface px-4 py-2.5 border-surface border-r w-[260px] shrink-0">
                            <span class="font-semibold text-fg-muted text-xs uppercase tracking-widest">Agent</span>
                        </div>
                        {{-- Hour ticks --}}
                        <div class="relative flex-none h-9" style="width: {{ $timelineWidth }}px">
                            @foreach ($hours as $hour)
                                @if ($hour['isHalf'])
                                    <div class="top-4 bottom-0 absolute flex items-end pb-1 border-dashed"
                                        style="left: {{ $hour['left'] }}px; border-color: var(--grid-line-faint)">
                                        <span
                                            class="pl-1 text-[9px] text-fg-muted whitespace-nowrap select-none">:30</span>
                                    </div>
                                @else
                                    <div class="top-0 bottom-0 absolute flex items-center border-l"
                                        style="left: {{ $hour['left'] }}px; border-color: var(--grid-line)">
                                        <span
                                            class="pl-1.5 font-medium text-[11px] text-fg-muted whitespace-nowrap select-none">
                                            {{ $hour['label'] }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- ── Groups + Agents ─────────────────────────────── --}}
                    @foreach ($grouped as $g)
                        {{-- Group separator --}}
                        <div wire:key="group-row-{{ optional($g['group'])->id ?? 'ungrouped' }}"
                            class="flex items-stretch bg-surface-3 border-b" style="border-color: var(--border)">
                            <div
                                class="left-0 z-10 sticky flex items-center gap-2 bg-surface-3 px-4 py-1.5 border-surface border-r w-[260px] shrink-0">
                                <span class="font-bold text-fg-muted text-xs uppercase tracking-widest">
                                    {{ optional($g['group'])->name ?? 'Unassigned' }}
                                </span>
                                <span class="font-medium text-[10px] text-fg-muted">{{ $g['count'] }}</span>
                            </div>
                            <div class="relative flex-none" style="width: {{ $timelineWidth }}px; height: 28px">
                                @foreach ($hours as $hour)
                                    <div class="absolute inset-y-0 {{ $hour['isHalf'] ? 'border-zinc-800/10 border-dashed' : 'border-zinc-800/20' }} border-l"
                                        style="left: {{ $hour['left'] }}px"></div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Agent rows --}}
                        @foreach ($g['agents'] as $agent)
                            @php
                                $currentStatus = $agent->agentStatus?->statusType;
                            @endphp
                            <div wire:key="agent-row-{{ $agent->id }}"
                                class="group flex items-stretch hover:bg-hover border-b transition-colors gantt-row-animate"
                                style="border-color: var(--border)" x-data="{
                                    userId: {{ $agent->id }},
                                    statusName: @js($currentStatus?->name ?? ''),
                                    statusColor: @js($currentStatus?->color ?? ''),
                                }"
                                @agent-row-update.window="if ($event.detail.user_id === userId) { statusName = $event.detail.status_name; statusColor = $event.detail.status_color; }"
                                style="animation-delay: {{ $loop->parent->index * 0.1 + $loop->index * 0.04 }}s">

                                {{-- Sticky left: agent info --}}
                                <div
                                    class="left-0 z-10 sticky flex items-center gap-2.5 bg-surface group-hover:bg-row-hover px-3 py-2 border-surface border-r w-[260px] transition-colors shrink-0">
                                    {{-- Avatar --}}
                                    <div class="flex justify-center items-center rounded-full w-8 h-8 font-bold text-[11px] uppercase select-none shrink-0"
                                        :style="statusColor ?
                                            `background-color:${statusColor}22; color:${statusColor}; border:1px solid ${statusColor}44` :
                                            'background-color:#6366f120; color:#818cf8; border:1px solid #6366f140'">
                                        {{ substr($agent->first_name, 0, 1) }}{{ substr($agent->last_name, 0, 1) }}
                                    </div>
                                    {{-- Name + badges + shift summary --}}
                                    <div class="flex-1 min-w-0">
                                        {{-- Name + match indicator --}}
                                        <div class="flex items-center gap-1 min-w-0">
                                            <span class="flex-1 min-w-0 font-medium text-fg-2 text-sm truncate">
                                                {{ $agent->first_name }} {{ $agent->last_name }}
                                            </span>
                                            @if ($agent->scheduledSlug)
                                                @if ($agent->isMatch)
                                                    <span style="color:#22c55e" title="On schedule">&#10003;</span>
                                                @else
                                                    <span style="color:#f59e0b" title="Off schedule">&#9888;</span>
                                                @endif
                                            @endif
                                        </div>
                                        {{-- Actual → Scheduled badges --}}
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span x-show="statusName"
                                                class="px-1.5 py-0.5 rounded font-semibold text-[10px] shrink-0"
                                                :style="statusColor ?
                                                    `background-color:${statusColor}22; color:${statusColor}` : ''"
                                                x-text="statusName"></span>
                                            <span x-show="!statusName"
                                                class="bg-surface-2 px-1.5 py-0.5 rounded font-semibold text-[10px] text-fg-muted shrink-0">Offline</span>
                                            <span class="text-[9px] text-fg-muted shrink-0">&#8594;</span>
                                            @if ($agent->scheduledLabel)
                                                <span class="px-1.5 py-0.5 rounded font-semibold text-[10px] shrink-0"
                                                    style="background-color:{{ $agent->scheduledColor }}22; color:{{ $agent->scheduledColor }}">
                                                    {{ $agent->scheduledLabel }}
                                                </span>
                                            @else
                                                <span
                                                    class="bg-surface-2 px-1.5 py-0.5 rounded font-semibold text-[10px] text-fg-muted shrink-0">No
                                                    Shift</span>
                                            @endif
                                        </div>
                                        {{-- Shift stats --}}
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="text-[10px] text-fg-muted">{{ $agent->shiftStartLabel }}
                                                &rarr;
                                                {{ $agent->shiftEndLabel }}</span>
                                            <span class="text-[10px] text-fg-muted">&middot;</span>
                                            <span
                                                class="text-[10px] font-medium {{ $agent->utilPct >= 70 ? 'text-accent-green' : ($agent->utilPct >= 40 ? 'text-accent-yellow' : 'text-accent-red') }}">{{ $agent->utilPct }}%</span>
                                            <span class="text-[10px] text-fg-muted">&middot;</span>
                                            <span
                                                class="text-[10px] text-fg-muted">{{ $agent->totalShiftLabel }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Timeline --}}
                                <div class="relative flex-none bg-surface group-hover:bg-row-hover transition-colors"
                                    style="width: {{ $timelineWidth }}px; height: 60px">

                                    {{-- Hour grid lines --}}
                                    @foreach ($hours as $hour)
                                        <div class="absolute inset-y-0 {{ $hour['isHalf'] ? 'border-dashed' : '' }} border-l"
                                            style="left: {{ $hour['left'] }}px; border-color: {{ $hour['isHalf'] ? 'var(--grid-line-faint)' : 'var(--grid-line)' }}">
                                        </div>
                                    @endforeach

                                    {{-- Status blocks --}}
                                    @foreach ($agent->timelineBlocks as $block)
                                        @php $statusName = strtolower($block['status'] ?? ''); @endphp
                                        @continue($statusName === 'offline')

                                        <div wire:key="block-{{ $agent->id }}-{{ $loop->index }}"
                                            class="gantt-block-animate top-2 bottom-2 absolute flex flex-col justify-center hover:brightness-110 px-1.5 rounded overflow-hidden transition-[filter] cursor-default"
                                            style="left: {{ $block['left'] }}px; width: {{ $block['width'] }}px; background-color: {{ $block['color'] }}; animation-delay: {{ $loop->index * 0.05 + 0.1 }}s"
                                            title="{{ $block['status'] }}: {{ $block['label'] }} → {{ $block['endLabel'] }} ({{ $block['durationLabel'] }})">
                                            @if ($block['width'] > 90)
                                                <div
                                                    class="drop-shadow-[0_1px_1px_rgba(0,0,0,0.5)] font-semibold text-[10px] text-white leading-none whitespace-nowrap">
                                                    {{ $block['label'] }} → {{ $block['endLabel'] }}
                                                </div>
                                                <div
                                                    class="drop-shadow-[0_1px_1px_rgba(0,0,0,0.4)] mt-0.5 text-[9px] text-white/80 leading-none whitespace-nowrap">
                                                    {{ $block['status'] }} · {{ $block['durationLabel'] }}
                                                </div>
                                            @elseif ($block['width'] > 50)
                                                <div
                                                    class="drop-shadow-[0_1px_1px_rgba(0,0,0,0.5)] font-semibold text-[10px] text-white leading-none whitespace-nowrap">
                                                    {{ $block['label'] }}
                                                </div>
                                                <div
                                                    class="drop-shadow-[0_1px_1px_rgba(0,0,0,0.4)] mt-0.5 text-[9px] text-white/80 leading-none whitespace-nowrap">
                                                    {{ $block['durationLabel'] }}
                                                </div>
                                            @elseif ($block['width'] > 22)
                                                <div
                                                    class="drop-shadow-[0_1px_1px_rgba(0,0,0,0.5)] font-semibold text-[9px] text-white leading-none whitespace-nowrap">
                                                    {{ $block['label'] }}
                                                </div>
                                            @endif
                                            @if ($block['isOngoing'])
                                                <div class="right-0 absolute inset-y-0 rounded-r w-8 animate-pulse pointer-events-none"
                                                    style="background: linear-gradient(to right, transparent, rgba(255,255,255,0.2))">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        @endforeach
                    @endforeach

                </div>{{-- /min-width wrapper --}}
            </div>{{-- /overflow-auto --}}

        @endif
    </div>{{-- /gantt card --}}

</div>
