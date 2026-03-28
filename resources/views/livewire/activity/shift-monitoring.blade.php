<div class="flex flex-col gap-4 p-6 h-full" x-data="{ isToday: @js($isToday) }" x-init="if (isToday) {
    const chan = window.Echo.private('agent-status');
    chan.listen('.AgentStatusUpdated', () => $wire.$refresh());
    $cleanup(() => chan.stopListening('.AgentStatusUpdated'));
}">

    {{-- ── Page header ──────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-3 shrink-0">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-bold text-zinc-100 text-xl">Shift Monitor</h1>
                @if ($isToday)
                    <span
                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2 py-0.5 rounded-full font-semibold text-[11px] text-green-400">
                        <span class="bg-green-400 rounded-full w-1.5 h-1.5 animate-pulse"></span>
                        LIVE
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-zinc-500 text-sm">Monitor scheduled slots and activity for day-to-day agents.</p>
        </div>
        <div class="text-right">
            <div class="font-semibold text-zinc-100 text-sm">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
            </div>
            <div class="mt-0.5 text-zinc-500 text-xs">{{ number_format($totalAgents) }} agents on shift</div>
        </div>
    </div>

    {{-- ── Controls ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search agents..."
            class="bg-zinc-900 px-3 py-1.5 border border-zinc-800 focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-48 text-white text-sm transition placeholder-zinc-600">

        <x-date-picker wire-model="date" :value="$date" />

        <select wire:model.live="filterStatus"
            class="bg-zinc-900 px-3 py-1.5 border border-zinc-800 focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 text-white text-sm transition">
            <option value="">All Statuses</option>
            @foreach ($statusTypes as $type)
                <option value="{{ $type->slug }}">{{ $type->name }}</option>
            @endforeach
        </select>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-2 ml-2">
            @foreach ($statusTypes as $type)
                <span class="inline-flex items-center gap-1.5 text-zinc-400 text-xs">
                    <span class="rounded-sm w-2.5 h-2.5 shrink-0"
                        style="background-color: {{ $type->color ?? '#6366f1' }}"></span>
                    {{ $type->name }}
                </span>
            @endforeach
        </div>
    </div>
    {{-- ── Stats strip ────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 text-xs shrink-0">
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="text-zinc-500">On Shift</span>
            <span class="font-semibold text-zinc-200">{{ $totalAgents }}</span>
        </div>
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="bg-green-400 rounded-full w-1.5 h-1.5 animate-pulse shrink-0"></span>
            <span class="text-zinc-500">Available Now</span>
            <span class="font-semibold text-green-400">{{ $availableNow }}</span>
        </div>
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="text-zinc-500">Avg Utilization</span>
            <span
                class="font-semibold {{ $avgUtil >= 70 ? 'text-green-400' : ($avgUtil >= 40 ? 'text-yellow-400' : 'text-red-400') }}">{{ $avgUtil }}%</span>
        </div>
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="text-zinc-500">Available Time</span>
            <span
                class="font-semibold text-zinc-200">{{ \App\Livewire\Activity\ShiftMonitoring::formatSeconds((int) $availSeconds) }}</span>
        </div>
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="text-zinc-500">Total Logged</span>
            <span
                class="font-semibold text-zinc-200">{{ \App\Livewire\Activity\ShiftMonitoring::formatSeconds((int) $totalSeconds) }}</span>
        </div>
    </div>
    {{-- ── Gantt Card ───────────────────────────────────────────── --}}
    <div class="flex flex-col flex-1 bg-zinc-900 border border-zinc-800 rounded-xl min-h-0 transition-opacity duration-200 [overflow:clip]"
        wire:loading.class.delay="opacity-50">

        @if ($grouped->isEmpty())
            <div class="flex flex-1 justify-center items-center py-20 text-zinc-500 text-sm italic">
                No shift data found for this date.
            </div>
        @else
            {{-- Scrollable Gantt body (both x + y) --}}
            <div class="flex-1 min-h-0 overflow-auto" style="scrollbar-gutter: stable" x-data x-init="const update = () => {
                $el.style.maxHeight = (window.innerHeight - $el.getBoundingClientRect().top - 24) + 'px';
            };
            update();
            window.addEventListener('resize', update);
            $cleanup(() => window.removeEventListener('resize', update));">

                {{-- Min-width wrapper so horizontal scroll works --}}
                <div class="relative" style="min-width: {{ 260 + $timelineWidth }}px">

                    {{-- "Now" time indicator --}}
                    @if ($isToday)
                        <div x-data="{
                            left: -1,
                            update() {
                                const elapsed = (Date.now() / 1000 - {{ $visibleStartTs }}) / 60;
                                const px = Math.round(elapsed * {{ $pxPerMin }});
                                this.left = (px >= 0 && px <= {{ $timelineWidth }}) ? (260 + px) : -1;
                            }
                        }" x-init="update();
                        const t = setInterval(() => update(), 30000);
                        $cleanup(() => clearInterval(t));"
                            class="top-0 bottom-0 z-[15] absolute w-0 pointer-events-none"
                            :class="{ 'hidden': left < 0 }" :style="\
                            `left: \${left}px\`">
                            <div class="opacity-70 w-px h-full"
                                style="background: linear-gradient(to bottom, #ef4444 0%, rgba(239,68,68,0.15) 100%)">
                            </div>
                            <div
                                class="top-9 absolute bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)] rounded-full w-2 h-2 -translate-x-1 animate-pulse">
                            </div>
                        </div>
                    @endif

                    {{-- ── Sticky header row ──────────────────────────── --}}
                    <div class="top-0 z-20 sticky flex items-stretch bg-zinc-900 border-zinc-800 border-b">
                        {{-- Corner cell (sticky left + top) --}}
                        <div
                            class="left-0 z-30 sticky flex items-center bg-zinc-900 px-4 py-2.5 border-zinc-800 border-r w-[260px] shrink-0">
                            <span class="font-semibold text-zinc-500 text-xs uppercase tracking-widest">Agent</span>
                        </div>
                        {{-- Hour ticks --}}
                        <div class="relative flex-none h-9" style="width: {{ $timelineWidth }}px">
                            @foreach ($hours as $hour)
                                @if ($hour['isHalf'])
                                    <div class="top-4 bottom-0 absolute flex items-end pb-1 border-zinc-700/25 border-l border-dashed"
                                        style="left: {{ $hour['left'] }}px">
                                        <span
                                            class="pl-1 text-[9px] text-zinc-700 whitespace-nowrap select-none">:30</span>
                                    </div>
                                @else
                                    <div class="top-0 bottom-0 absolute flex items-center border-zinc-800/60 border-l"
                                        style="left: {{ $hour['left'] }}px">
                                        <span
                                            class="pl-1.5 font-medium text-[11px] text-zinc-500 whitespace-nowrap select-none">
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
                            class="flex items-stretch bg-[#0d0f13] border-zinc-800/80 border-b">
                            <div
                                class="left-0 z-10 sticky flex items-center gap-2 bg-[#0d0f13] px-4 py-1.5 border-zinc-800 border-r w-[260px] shrink-0">
                                <span class="font-bold text-zinc-400 text-xs uppercase tracking-widest">
                                    {{ optional($g['group'])->name ?? 'Unassigned' }}
                                </span>
                                <span class="font-medium text-[10px] text-zinc-600">{{ $g['count'] }}</span>
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
                                class="group flex items-stretch hover:bg-white/[0.02] border-zinc-800/40 border-b transition-colors gantt-row-animate"
                                style="animation-delay: {{ $loop->parent->index * 0.1 + $loop->index * 0.04 }}s">

                                {{-- Sticky left: agent info --}}
                                <div
                                    class="left-0 z-10 sticky flex items-center gap-2.5 bg-zinc-900 group-hover:bg-[#1a1d24] px-3 py-2 border-zinc-800 border-r w-[260px] transition-colors shrink-0">
                                    {{-- Avatar --}}
                                    <div class="flex justify-center items-center rounded-full w-8 h-8 font-bold text-[11px] uppercase select-none shrink-0"
                                        style="background-color: {{ $currentStatus?->color ? $currentStatus->color . '22' : '#6366f120' }}; color: {{ $currentStatus?->color ?? '#818cf8' }}; border: 1px solid {{ $currentStatus?->color ? $currentStatus->color . '44' : '#6366f140' }}">
                                        {{ substr($agent->first_name, 0, 1) }}{{ substr($agent->last_name, 0, 1) }}
                                    </div>
                                    {{-- Name + shift summary --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="flex-1 min-w-0 font-medium text-zinc-200 text-sm truncate">
                                                {{ $agent->first_name }} {{ $agent->last_name }}
                                            </span>
                                            @if ($currentStatus)
                                                <span class="px-1.5 py-0.5 rounded font-semibold text-[10px] shrink-0"
                                                    style="background-color: {{ $currentStatus->color }}22; color: {{ $currentStatus->color }}">
                                                    {{ $currentStatus->name }}
                                                </span>
                                            @else
                                                <span
                                                    class="bg-zinc-800 px-1.5 py-0.5 rounded font-semibold text-[10px] text-zinc-500 shrink-0">
                                                    Offline
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="text-[10px] text-zinc-600">{{ $agent->shiftStartLabel }} →
                                                {{ $agent->shiftEndLabel }}</span>
                                            <span class="text-[10px] text-zinc-800">·</span>
                                            <span
                                                class="text-[10px] font-medium {{ $agent->utilPct >= 70 ? 'text-green-500' : ($agent->utilPct >= 40 ? 'text-yellow-500' : 'text-red-500') }}">{{ $agent->utilPct }}%</span>
                                            <span class="text-[10px] text-zinc-800">·</span>
                                            <span
                                                class="text-[10px] text-zinc-600">{{ $agent->totalShiftLabel }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Timeline --}}
                                <div class="relative flex-none bg-zinc-900 group-hover:bg-[#1a1d24] transition-colors"
                                    style="width: {{ $timelineWidth }}px; height: 60px">

                                    {{-- Hour grid lines --}}
                                    @foreach ($hours as $hour)
                                        <div class="absolute inset-y-0 {{ $hour['isHalf'] ? 'border-zinc-800/15 border-dashed' : 'border-zinc-800/25' }} border-l"
                                            style="left: {{ $hour['left'] }}px"></div>
                                    @endforeach

                                    {{-- Status blocks --}}
                                    @foreach ($agent->timelineBlocks as $block)
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
