<div class="flex flex-col gap-4 p-6 h-full">

    {{-- ── Page header ──────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-3 shrink-0">
        <div>
            <h1 class="font-bold text-zinc-100 text-xl">Shift Monitor</h1>
            <p class="mt-0.5 text-zinc-500 text-sm">Monitor scheduled slots and activity for day-to-day agents.</p>
        </div>
        <div class="text-right">
            <div class="font-semibold text-zinc-100 text-sm">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</div>
            <div class="mt-0.5 text-zinc-500 text-xs">{{ number_format($totalAgents) }} agents on shift</div>
        </div>
    </div>

    {{-- ── Controls ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search agents..."
            class="bg-zinc-900 px-3 py-1.5 border border-zinc-800 focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-48 text-white text-sm transition placeholder-zinc-600">

        <input type="date" wire:model.live="date"
            class="bg-zinc-900 px-3 py-1.5 border border-zinc-800 focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 text-white text-sm transition">

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

    {{-- ── Gantt Card ───────────────────────────────────────────── --}}
    <div class="flex flex-col flex-1 bg-zinc-900 border border-zinc-800 rounded-xl min-h-0 [overflow:clip]">

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
                <div style="min-width: {{ 260 + $timelineWidth }}px">

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
                                <div class="top-0 bottom-0 absolute flex items-center border-zinc-800/60 border-l"
                                    style="left: {{ $hour['left'] }}px">
                                    <span
                                        class="pl-1.5 font-medium text-[11px] text-zinc-500 whitespace-nowrap select-none">
                                        {{ $hour['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── Groups + Agents ─────────────────────────────── --}}
                    @foreach ($grouped as $g)
                        {{-- Group separator --}}
                        <div class="flex items-stretch bg-[#0d0f13] border-zinc-800/80 border-b">
                            <div
                                class="left-0 z-10 sticky flex items-center gap-2 bg-[#0d0f13] px-4 py-1.5 border-zinc-800 border-r w-[260px] shrink-0">
                                <span class="font-bold text-zinc-400 text-xs uppercase tracking-widest">
                                    {{ optional($g['group'])->name ?? 'Unassigned' }}
                                </span>
                                <span class="font-medium text-[10px] text-zinc-600">{{ $g['count'] }}</span>
                            </div>
                            <div class="relative flex-none" style="width: {{ $timelineWidth }}px; height: 28px">
                                @foreach ($hours as $hour)
                                    <div class="absolute inset-y-0 border-zinc-800/20 border-l"
                                        style="left: {{ $hour['left'] }}px"></div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Agent rows --}}
                        @foreach ($g['agents'] as $agent)
                            @php
                                $currentStatus = $agent->agentStatus?->statusType;
                            @endphp
                            <div
                                class="group flex items-stretch hover:bg-white/[0.02] border-zinc-800/40 border-b transition-colors">

                                {{-- Sticky left: agent info --}}
                                <div
                                    class="left-0 z-10 sticky flex items-center gap-2.5 bg-zinc-900 group-hover:bg-[#1a1d24] px-3 py-2 border-zinc-800 border-r w-[260px] transition-colors shrink-0">
                                    {{-- Avatar --}}
                                    <div class="flex justify-center items-center rounded-full w-7 h-7 font-bold text-[11px] uppercase select-none shrink-0"
                                        style="background-color: {{ $currentStatus?->color ? $currentStatus->color . '22' : '#6366f120' }}; color: {{ $currentStatus?->color ?? '#818cf8' }}; border: 1px solid {{ $currentStatus?->color ? $currentStatus->color . '44' : '#6366f140' }}">
                                        {{ substr($agent->first_name, 0, 1) }}{{ substr($agent->last_name, 0, 1) }}
                                    </div>
                                    {{-- Name --}}
                                    <span class="flex-1 min-w-0 font-medium text-zinc-200 text-sm truncate">
                                        {{ $agent->first_name }} {{ $agent->last_name }}
                                    </span>
                                    {{-- Status badge --}}
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

                                {{-- Timeline --}}
                                <div class="relative flex-none bg-zinc-900 group-hover:bg-[#1a1d24] transition-colors"
                                    style="width: {{ $timelineWidth }}px; height: 44px">

                                    {{-- Hour grid lines --}}
                                    @foreach ($hours as $hour)
                                        <div class="absolute inset-y-0 border-zinc-800/25 border-l"
                                            style="left: {{ $hour['left'] }}px"></div>
                                    @endforeach

                                    {{-- Status blocks --}}
                                    @foreach ($agent->timelineBlocks as $block)
                                        <div class="top-2.5 bottom-2.5 absolute flex items-center hover:brightness-110 px-1.5 rounded overflow-hidden transition-[filter] cursor-default"
                                            style="left: {{ $block['left'] }}px; width: {{ $block['width'] }}px; background-color: {{ $block['color'] }}"
                                            title="{{ $block['status'] }}: {{ $block['label'] }} ({{ $block['durationLabel'] }})">
                                            @if ($block['width'] > 28)
                                                <span
                                                    class="drop-shadow-[0_1px_1px_rgba(0,0,0,0.5)] font-semibold text-[10px] text-white leading-none whitespace-nowrap">
                                                    {{ $block['label'] }}
                                                </span>
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
