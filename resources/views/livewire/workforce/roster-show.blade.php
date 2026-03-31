<div class="flex flex-col h-full">

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-3 px-6 pt-6 pb-4 border-surface border-b shrink-0">

        {{-- Back + title --}}
        <div class="flex items-start gap-3">
            <a href="{{ route('workforce.rosters.index') }}" wire:navigate
                class="flex justify-center items-center bg-surface hover:bg-hover mt-0.5 border border-surface rounded-md w-7 h-7 text-fg-muted hover:text-fg transition shrink-0">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
            </a>
            <div>
                <h1 class="font-bold text-fg text-xl leading-tight">
                    Weekly Roster
                    <span class="font-normal text-fg-muted">/ {{ $roster->label }}</span>
                </h1>
                <p class="mt-0.5 text-fg-muted text-xs">
                    @if ($campaign)
                        <span class="font-medium text-fg-2">{{ $campaign->name }}</span>
                        &nbsp;&middot;&nbsp;
                    @endif
                    {{ $roster->week_start->format('D, d M Y') }}
                    &ndash;
                    {{ $roster->week_end->format('D, d M Y') }}
                    &nbsp;|&nbsp;{{ $roster->timezone }}
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            @if ($roster->status === 'published')
                <span
                    class="inline-flex items-center gap-1.5 bg-green-500/10 px-3 py-1.5 rounded-md font-bold text-xs uppercase tracking-wide text-accent-green">
                    <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Published
                </span>
                <button wire:click="unpublish"
                    class="inline-flex items-center gap-2 bg-surface-2 hover:bg-hover px-4 py-1.5 border border-surface rounded-md font-medium text-fg-muted hover:text-fg text-sm transition">
                    Unpublish
                </button>
            @else
                <span
                    class="inline-flex items-center gap-1.5 bg-surface-2 px-3 py-1.5 rounded-md font-bold text-fg-muted text-xs uppercase tracking-wide">
                    <span class="bg-surface-3 rounded-full w-1.5 h-1.5"></span>Draft
                </span>
                <button wire:click="publish"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-1.5 rounded-md font-medium text-white text-sm transition">
                    Publish
                </button>
            @endif
        </div>
    </div>

    {{-- ── Tab bar ──────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-1 px-6 pt-3 border-surface border-b overflow-x-auto shrink-0">
        @php
            $tabBase = 'px-3 py-2 text-sm font-medium rounded-t-md transition whitespace-nowrap cursor-pointer';
            $tabActive = $tabBase . ' text-fg border-b-2 border-blue-500';
            $tabInactive = $tabBase . ' text-fg-muted hover:text-fg';
        @endphp

        <button wire:click="setTab('summary')"
            class="{{ $activeTab === 'summary' ? $tabActive : $tabInactive }}">Summary</button>

        <button wire:click="setTab('details')"
            class="{{ $activeTab === 'details' ? $tabActive : $tabInactive }}">Details</button>

        @foreach ($days as $d => $dayName)
            <button wire:click="setDay({{ $d }})"
                class="{{ $activeTab === 'day' && $activeDay === $d ? $tabActive : $tabInactive }}">
                {{ $dayName }}
            </button>
        @endforeach
    </div>

    {{-- ── Tab content ─────────────────────────────────────────────── --}}
    <div class="flex-1 p-6 overflow-auto">

        {{-- ════════════════ SUMMARY TAB ════════════════ --}}
        @if ($activeTab === 'summary')
            <div class="gap-4 grid grid-cols-1 xl:grid-cols-2">

                {{-- Staffing Index --}}
                <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
                    <div class="px-5 py-4 border-surface border-b">
                        <h3 class="font-semibold text-fg text-sm">Staffing Index</h3>
                        <p class="mt-0.5 text-fg-muted text-xs">Summary of staffing against forecast required levels.
                        </p>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr
                                class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                                <th class="px-5 py-3 text-left">Day</th>
                                <th class="px-5 py-3 text-left">Staffing Index</th>
                                <th class="px-5 py-3 text-right">Understaffed Intervals</th>
                                <th class="px-5 py-3 text-right">Understaffed Agents</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summaryData as $d => $row)
                                <tr class="hover:bg-hover border-surface border-b transition">
                                    <td class="px-5 py-3 font-medium text-fg">{{ $row['day'] }}</td>
                                    <td class="px-5 py-3">
                                        @if ($row['staffing_index'] !== null)
                                            @php
                                                $idx = $row['staffing_index'];
                                                $barColor =
                                                    $idx >= 95
                                                        ? 'bg-green-500'
                                                        : ($idx >= 85
                                                            ? 'bg-yellow-400'
                                                            : ($idx >= 75
                                                                ? 'bg-yellow-500'
                                                                : 'bg-pink-500'));
                                                $textColor = $idx >= 75 ? 'text-accent-green' : 'text-accent-red';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="bg-surface-2 rounded-full w-28 h-2">
                                                    <div class="{{ $barColor }} h-2 rounded-full"
                                                        style="width: {{ min($idx, 100) }}%"></div>
                                                </div>
                                                <span
                                                    class="text-xs font-bold {{ $textColor }}">{{ $idx }}%</span>
                                            </div>
                                        @else
                                            <span class="text-fg-muted text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-fg-muted text-sm text-right">
                                        {{ $row['understaffed_intervals'] ?: '—' }}</td>
                                    <td class="px-5 py-3 text-fg-muted text-sm text-right">
                                        {{ $row['understaffed_agents'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Staff By Location --}}
                <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
                    <div class="px-5 py-4 border-surface border-b">
                        <h3 class="font-semibold text-fg text-sm">Staff By Location</h3>
                        <p class="mt-0.5 text-fg-muted text-xs">Rostered staff by location.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr
                                    class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                                    <th class="px-5 py-3 text-left">Location</th>
                                    @foreach ($dayAbbr as $abbr)
                                        <th class="px-3 py-3 text-center">{{ $abbr }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($locationData as $row)
                                    <tr class="hover:bg-hover border-surface border-b transition">
                                        <td class="px-5 py-3 font-semibold text-fg">{{ $row['location'] }}</td>
                                        @for ($d = 0; $d <= 6; $d++)
                                            <td class="px-3 py-3 text-fg-muted text-sm text-center">
                                                {{ $row[$d] > 0 ? $row[$d] : '—' }}
                                            </td>
                                        @endfor
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-5 py-12 text-fg-muted text-sm text-center italic">
                                            No shifts added yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        @endif

        {{-- ════════════════ DETAILS TAB ════════════════ --}}
        @if ($activeTab === 'details')
            @forelse ($detailsByLocation as $location => $rows)
                <div class="bg-surface mb-4 border border-surface rounded-xl [overflow:clip]">
                    <div class="px-5 py-4 border-surface border-b">
                        <h3 class="font-semibold text-fg text-sm">{{ $location }} Shift Summary</h3>
                        <p class="mt-0.5 text-fg-muted text-xs">Summary of rostered staff at this location.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr
                                    class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                                    <th class="px-5 py-3 text-left">User</th>
                                    <th class="px-4 py-3 text-right">Hrs</th>
                                    @foreach ($days as $dayName)
                                        <th class="px-4 py-3 text-center">{{ substr($dayName, 0, 3) }}</th>
                                    @endforeach
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr class="group hover:bg-hover border-surface border-b transition">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="flex justify-center items-center bg-blue-600 rounded-full w-7 h-7 font-bold text-white text-xs shrink-0">
                                                    {{ strtoupper(substr($row['user']->first_name ?? '?', 0, 1) . substr($row['user']->last_name ?? '', 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-fg">
                                                    {{ $row['user']->first_name }} {{ $row['user']->last_name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-fg-muted text-xs text-right">
                                            {{ $row['total_hours'] ? number_format($row['total_hours'], 1) . 'H' : '—' }}
                                        </td>
                                        @for ($d = 0; $d <= 6; $d++)
                                            @php
                                                $shiftObj = $roster->shifts
                                                    ->where('user_id', $row['user']->id)
                                                    ->where('day_of_week', $d)
                                                    ->where('location', $location)
                                                    ->first();
                                            @endphp
                                            <td class="px-4 py-3 text-fg-muted text-xs text-center whitespace-nowrap">
                                                @if ($shiftObj)
                                                    {{-- click cell → jump to that day view --}}
                                                    <button wire:click="setDay({{ $d }})"
                                                        class="hover:text-blue-400 transition"
                                                        title="View {{ $days[$d] }}">
                                                        {{ $shiftObj->shift_label }}
                                                    </button>
                                                @else
                                                    <span class="opacity-40">—</span>
                                                @endif
                                            </td>
                                        @endfor
                                        <td class="px-4 py-3"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div
                    class="bg-surface px-5 py-12 border border-surface rounded-xl text-fg-muted text-sm text-center italic">
                    No shift details yet. Add shifts to see this breakdown.
                </div>
            @endforelse
        @endif

        {{-- ════════════════ DAY TAB (Gantt) ════════════════ --}}
        @if ($activeTab === 'day')
            @php $dv = $dayViewData; @endphp

            {{-- ── Bar chart ──────────────────────────────────── --}}
            @if (!empty($dv['intervals']))
                @php
                    $chartH = 200;
                    $yLblW = 48;
                    $ivs = $dv['intervals'];
                    $colW = $dv['pxPerMin'] * 15;
                    $svgW = $dv['timelineWidth'];

                    // Y range
                    $allCVals = array_merge(
                        array_column($ivs, 'phones_rostered'),
                        array_column($ivs, 'phones_required'),
                        array_column($ivs, 'net'),
                        array_column($ivs, 'calls'),
                    );
                    $cMax = !empty($allCVals) ? max(0, ...$allCVals) : 40;
                    $cMin = !empty($allCVals) ? min(0, ...$allCVals) : 0;
                    $topV = (int) (ceil(($cMax + 2) / 5) * 5);
                    $botV = (int) (floor((min(-1, $cMin) - 1) / 5) * 5);
                    $cRange = max(1, $topV - $botV);
                    $ppu = $chartH / $cRange;
                    $zeroY = round(($topV / $cRange) * $chartH, 2);

                    // Y ticks
                    $tStep = $cRange <= 20 ? 5 : ($cRange <= 60 ? 10 : 20);
                    $yTicks = [];
                    for ($t = $botV; $t <= $topV; $t += $tStep) {
                        $yTicks[] = $t;
                    }

                    // Bar geometry
                    $nS = 4;
                    $bGap = 0.8;
                    $bw = max(2.0, ($colW - ($nS + 1) * $bGap) / $nS);

                    $cSeries = [
                        ['key' => 'phones_rostered', 'color' => '#e879f9', 'label' => 'Agents Rostered (Phones)'],
                        ['key' => 'phones_required', 'color' => '#38bdf8', 'label' => 'Agents Required (Phones)'],
                        ['key' => 'net', 'color' => '#6366f1', 'label' => 'Net Staffing'],
                        ['key' => 'calls', 'color' => '#2dd4bf', 'label' => 'Calls'],
                    ];
                @endphp

                <div class="bg-surface mb-4 border border-surface rounded-xl [overflow:clip]">
                    <div class="overflow-x-auto">
                        <div style="min-width: {{ $yLblW + $svgW + 16 }}px; padding: 14px 8px 0">

                            {{-- Chart rows --}}
                            <div class="flex" style="height: {{ $chartH }}px">

                                {{-- Y-axis labels --}}
                                <div class="relative shrink-0" style="width: {{ $yLblW }}px">
                                    @foreach ($yTicks as $tick)
                                        @php $ty = round($zeroY - $tick * $ppu, 1); @endphp
                                        <span
                                            class="right-2 absolute font-medium text-[10px] text-fg-muted -translate-y-1/2"
                                            style="top: {{ $ty }}px">{{ $tick }}</span>
                                    @endforeach
                                </div>

                                {{-- SVG bars --}}
                                <svg width="{{ $svgW }}" height="{{ $chartH }}"
                                    style="display:block;flex-shrink:0;overflow:visible">

                                    {{-- Horizontal grid lines --}}
                                    @foreach ($yTicks as $tick)
                                        @php $ty = round($zeroY - $tick * $ppu, 1); @endphp
                                        <line x1="0" y1="{{ $ty }}" x2="{{ $svgW }}"
                                            y2="{{ $ty }}"
                                            stroke="{{ $tick === 0 ? 'rgba(255,255,255,0.15)' : 'rgba(255,255,255,0.04)' }}"
                                            stroke-width="{{ $tick === 0 ? 1.5 : 1 }}" />
                                    @endforeach

                                    {{-- Bars per interval --}}
                                    @foreach ($ivs as $iv)
                                        @foreach ($cSeries as $si => $s)
                                            @php
                                                $val = $iv[$s['key']];
                                                $bx = round($iv['left'] + $bGap + $si * ($bw + $bGap), 2);
                                                $by = $val >= 0 ? round($zeroY - $val * $ppu, 2) : $zeroY;
                                                $bh = round(abs($val) * $ppu, 2);
                                            @endphp
                                            @if ($val !== 0 && $bh > 0)
                                                <rect x="{{ $bx }}" y="{{ $by }}"
                                                    width="{{ round($bw, 2) }}" height="{{ $bh }}"
                                                    fill="{{ $s['color'] }}" rx="1" opacity="0.9" />
                                            @endif
                                        @endforeach
                                    @endforeach

                                </svg>
                            </div>

                            {{-- X-axis time labels --}}
                            <div class="relative"
                                style="padding-left:{{ $yLblW }}px;height:22px;margin-top:3px">
                                <div class="relative" style="width:{{ $svgW }}px">
                                    @foreach ($dv['hours'] as $hour)
                                        <span class="absolute whitespace-nowrap"
                                            style="left:{{ $hour['left'] }}px;transform:translateX(-50%);font-size:9px;color:{{ $hour['isHalf'] ? 'var(--fg-muted-faint, #555)' : 'var(--fg-muted)' }}">{{ $hour['label'] }}</span>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        {{-- Legend --}}
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 px-4 py-3 border-t"
                            style="border-color:var(--border)">
                            @foreach ($cSeries as $s)
                                <span class="inline-flex items-center gap-1.5 text-fg-muted text-xs">
                                    <span class="inline-block rounded-sm w-2.5 h-2.5 shrink-0"
                                        style="background-color:{{ $s['color'] }}"></span>
                                    {{ $s['label'] }}
                                </span>
                            @endforeach
                        </div>

                    </div>
                </div>
            @endif

            {{-- Interval stats grid --}}
            @if (!empty($dv['intervals']))
                <div class="bg-surface mb-4 border border-surface rounded-xl [overflow:clip]">
                    <div class="overflow-x-auto">
                        <div style="min-width: {{ $dv['timelineWidth'] + 220 }}px">

                            <div class="relative flex items-end bg-surface border-surface border-b"
                                style="height: 32px; padding-left: 220px">
                                <div class="relative" style="width: {{ $dv['timelineWidth'] }}px; height: 100%">
                                    @foreach ($dv['hours'] as $hour)
                                        <span class="bottom-1 absolute whitespace-nowrap"
                                            style="left: {{ $hour['left'] }}px; transform: translateX(-50%); font-size: {{ $hour['isHalf'] ? '9px' : '10px' }}; color: var(--fg-muted){{ $hour['isHalf'] ? '; opacity: 0.55' : '' }}">
                                            {{ $hour['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            @php
                                $statRows = [
                                    [
                                        'label' => 'Agents Rostered (Phones)',
                                        'key' => 'phones_rostered',
                                        'color' => 'text-fuchsia-400',
                                    ],
                                    [
                                        'label' => 'Agents Required (Phones)',
                                        'key' => 'phones_required',
                                        'color' => 'text-sky-400',
                                    ],
                                    ['label' => 'Net Staffing', 'key' => 'net', 'color' => null],
                                    ['label' => 'Calls', 'key' => 'calls', 'color' => 'text-teal-400'],
                                ];
                            @endphp

                            @foreach ($statRows as $statRow)
                                <div class="flex items-center border-surface last:border-0 border-b"
                                    style="height: 28px">
                                    <div class="px-4 font-medium text-fg-muted text-xs shrink-0" style="width: 220px">
                                        {{ $statRow['label'] }}
                                    </div>
                                    <div class="relative" style="width: {{ $dv['timelineWidth'] }}px; height: 100%">
                                        @foreach ($dv['intervals'] as $interval)
                                            @php
                                                $val = $interval[$statRow['key']];
                                                $tc = $statRow['color'] ?? 'text-fg';
                                                if ($statRow['key'] === 'net') {
                                                    $tc =
                                                        $val > 0
                                                            ? 'text-accent-green'
                                                            : ($val < 0
                                                                ? 'text-accent-red'
                                                                : 'text-fg-muted');
                                                }
                                            @endphp
                                            <span class="absolute text-[11px] font-semibold {{ $tc }}"
                                                style="left: {{ $interval['left'] + 2 }}px; top: 50%; transform: translateY(-50%)">
                                                {{ $val !== 0 ? $val : '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            @endif

            {{-- Agent Gantt --}}
            @if (empty($dv['agents']))
                <div
                    class="bg-surface px-5 py-16 border border-surface rounded-xl text-fg-muted text-sm text-center italic">
                    No agents found for this campaign.
                </div>
            @else
                @php $byLocation = collect($dv['agents'])->groupBy('location'); @endphp

                {{-- ── Filter bar ──────────────────────────────────────── --}}
                <div class="flex flex-wrap items-center gap-2 mb-3">

                    {{-- Search --}}
                    <div class="relative flex items-center min-w-[180px]">
                        <x-heroicon-o-magnifying-glass
                            class="left-2.5 absolute w-3.5 h-3.5 text-fg-muted pointer-events-none" />
                        <x-input wire:model.live.debounce.200ms="filterSearch" type="text"
                            placeholder="Search agent…" class="pl-8 w-full" />
                        @if ($filterSearch)
                            <button wire:click="$set('filterSearch','')" type="button"
                                class="right-2.5 absolute text-fg-muted hover:text-fg transition">
                                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>

                    {{-- Group filter --}}
                    <x-select-dropdown wire-model="filterGroup" :value="$filterGroup" placeholder="Group"
                        :options="$groupOptions" />

                    {{-- Location filter --}}
                    <x-select-dropdown wire-model="filterLocation" :value="$filterLocation" placeholder="Location"
                        :options="$locationOptions" />

                    {{-- Clear --}}
                    @if ($filterSearch || $filterGroup || $filterLocation)
                        <button wire:click="$set('filterSearch',''); $set('filterGroup',''); $set('filterLocation','')"
                            type="button"
                            class="inline-flex items-center gap-1 text-fg-muted hover:text-fg text-xs transition">
                            <x-heroicon-o-x-mark class="w-3 h-3" />
                            Clear filters
                        </button>
                    @endif

                </div>

                <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
                    {{-- Scrollable Gantt (both axes, drag-to-scroll) --}}
                    <div class="overflow-auto select-none no-scrollbar" style="cursor: grab" x-data="{
                        dragging: false,
                        startX: 0,
                        startY: 0,
                        scrollLeft: 0,
                        scrollTop: 0,
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
                    }"
                        @mousedown="onDown($event)" @mousemove="onMove($event)" @mouseup="onUp()"
                        @mouseleave="onUp()">

                        <div class="relative" style="min-width: {{ 260 + $dv['timelineWidth'] }}px">

                            {{-- ── Sticky header row ──────────────────────────── --}}
                            <div class="top-0 z-20 sticky flex items-stretch bg-surface border-b"
                                style="border-color: var(--border)">
                                <div
                                    class="left-0 z-30 sticky flex items-center bg-surface px-4 py-2.5 border-surface border-r w-[260px] shrink-0">
                                    <span
                                        class="font-semibold text-fg-muted text-xs uppercase tracking-widest">Agent</span>
                                </div>
                                <div class="relative flex-none h-9" style="width: {{ $dv['timelineWidth'] }}px">
                                    @foreach ($dv['hours'] as $hour)
                                        @if ($hour['isHalf'])
                                            <div class="top-4 bottom-0 absolute flex items-end pb-1 border-l border-dashed"
                                                style="left: {{ $hour['left'] }}px; border-color: var(--grid-line-faint)">
                                                <span class="pl-1 text-[9px] text-fg-muted select-none">:30</span>
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

                            {{-- ── Locations + Agents ──────────────────────────── --}}
                            @foreach ($byLocation as $location => $locationAgents)
                                {{-- Location separator --}}
                                <div class="flex items-stretch bg-surface-3 border-b"
                                    style="border-color: var(--border)">
                                    <div
                                        class="left-0 z-10 sticky flex items-center gap-2 bg-surface-3 px-4 py-1.5 border-surface border-r w-[260px] shrink-0">
                                        <span class="font-bold text-fg-muted text-xs uppercase tracking-widest">
                                            {{ $location ?: 'Unassigned' }}
                                        </span>
                                        <span
                                            class="font-medium text-[10px] text-fg-muted">{{ count($locationAgents) }}</span>
                                    </div>
                                    <div class="relative flex-none"
                                        style="width: {{ $dv['timelineWidth'] }}px; height: 28px">
                                        @foreach ($dv['hours'] as $hour)
                                            <div class="absolute inset-y-0 {{ $hour['isHalf'] ? 'border-zinc-800/10 border-dashed' : 'border-zinc-800/20' }} border-l"
                                                style="left: {{ $hour['left'] }}px"></div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Agent rows --}}
                                @foreach ($locationAgents as $agent)
                                    <div class="group flex items-stretch hover:bg-hover border-b transition-colors"
                                        style="border-color: var(--border)">

                                        {{-- Sticky left: agent info --}}
                                        <div
                                            class="left-0 z-10 sticky flex items-center gap-2.5 bg-surface group-hover:bg-row-hover px-3 py-2 border-surface border-r w-[260px] transition-colors shrink-0">
                                            <div class="flex justify-center items-center rounded-full w-8 h-8 font-bold text-[11px] uppercase select-none shrink-0"
                                                style="background-color: #6366f120; color: #818cf8; border: 1px solid #6366f140">
                                                {{ strtoupper(substr($agent['user']?->first_name ?? '?', 0, 1) . substr($agent['user']?->last_name ?? '', 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-baseline gap-1.5 min-w-0">
                                                    <span class="font-medium text-fg-2 text-sm truncate">
                                                        {{ $agent['user']?->first_name }}
                                                        {{ $agent['user']?->last_name }}
                                                    </span>
                                                    @if ($agent['hours'])
                                                        <span class="font-semibold text-[10px] shrink-0"
                                                            style="color: #818cf8">{{ number_format($agent['hours'], 1) }}h</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button
                                                wire:click="openAddActivity({{ $agent['user']->id }}, {{ $activeDay }})"
                                                class="flex justify-center items-center hover:bg-surface-2 opacity-0 group-hover:opacity-100 rounded w-5 h-5 text-fg-muted hover:text-blue-400 transition shrink-0"
                                                title="Add activity block">
                                                <x-heroicon-o-plus class="w-3 h-3" />
                                            </button>
                                        </div>

                                        {{-- Timeline — click anywhere to add activity at that time --}}
                                        <div class="relative flex-none bg-surface group-hover:bg-row-hover transition-colors"
                                            style="width: {{ $dv['timelineWidth'] }}px; height: 60px; cursor: crosshair"
                                            x-data="{
                                                hoverX: -1,
                                                hoverLabel: '',
                                                _dX: 0,
                                                _dY: 0,
                                                ppm: {{ $dv['pxPerMin'] }},
                                                vsm: {{ $dv['visibleStartMinutes'] }},
                                                posX(e) { return Math.max(0, e.clientX - $el.getBoundingClientRect().left); },
                                                toHHMM(x) {
                                                    const snap = Math.round(x / this.ppm / 30) * 30;
                                                    const tot = this.vsm + snap;
                                                    const h = Math.floor(tot / 60) % 24,
                                                        m = tot % 60;
                                                    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                                                },
                                                toFmt(x) {
                                                    const snap = Math.round(x / this.ppm / 30) * 30;
                                                    const tot = this.vsm + snap;
                                                    const h = Math.floor(tot / 60),
                                                        m = tot % 60;
                                                    return (h % 12 || 12) + (m ? ':' + String(m).padStart(2, '0') : '') + (h >= 12 ? 'pm' : 'am');
                                                }
                                            }"
                                            @mousedown="_dX = $event.pageX; _dY = $event.pageY"
                                            @click="
                                                if ($event.target.closest('[data-block]')) return;
                                                if (Math.abs($event.pageX - _dX) > 5 || Math.abs($event.pageY - _dY) > 5) return;
                                                $wire.openAddActivity({{ $agent['user']->id }}, {{ $activeDay }}, toHHMM(posX($event)))
                                            "
                                            @mousemove="
                                                if ($event.target.closest('[data-block]')) { hoverX = -1; return; }
                                                hoverX = posX($event); hoverLabel = toFmt(hoverX);
                                            "
                                            @mouseleave="hoverX = -1">

                                            {{-- Hour grid lines --}}
                                            @foreach ($dv['hours'] as $hour)
                                                <div class="absolute inset-y-0 {{ $hour['isHalf'] ? 'border-dashed' : '' }} border-l"
                                                    style="left: {{ $hour['left'] }}px; border-color: {{ $hour['isHalf'] ? 'var(--grid-line-faint)' : 'var(--grid-line)' }}">
                                                </div>
                                            @endforeach

                                            {{-- Ghost time cursor on hover --}}
                                            <div x-show="hoverX >= 0"
                                                class="z-10 absolute inset-y-0 pointer-events-none"
                                                :style="'left: ' + hoverX + 'px'">
                                                <div class="absolute inset-y-0 bg-blue-400/50 w-px"></div>
                                                <span x-text="hoverLabel"
                                                    class="top-1 left-1 absolute bg-surface/90 px-1 rounded font-semibold text-[9px] text-blue-400 whitespace-nowrap select-none"></span>
                                            </div>

                                            {{-- Activity blocks --}}
                                            @foreach ($agent['blocks'] as $block)
                                                @if ($block['id'] ?? null)
                                                    {{-- Draggable / resizable activity block --}}
                                                    <div data-block x-data="{
                                                        id: {{ $block['id'] }},
                                                        left: {{ $block['left'] }},
                                                        width: {{ $block['width'] }},
                                                        ppm: {{ $dv['pxPerMin'] }},
                                                        vsm: {{ $dv['visibleStartMinutes'] }},
                                                        mode: null,
                                                        startX: 0,
                                                        origLeft: 0,
                                                        origWidth: 0,
                                                        dragMoved: false,
                                                        sFmt() {
                                                            const t = this.vsm + (this.left / this.ppm);
                                                            const h = Math.floor(t / 60) % 24,
                                                                m = Math.round(t % 60);
                                                            return (h % 12 || 12) + (m ? ':' + String(m).padStart(2, '0') : '') + (h >= 12 ? 'pm' : 'am');
                                                        },
                                                        eFmt() {
                                                            const t = this.vsm + ((this.left + this.width) / this.ppm);
                                                            const h = Math.floor(t / 60) % 24,
                                                                m = Math.round(t % 60);
                                                            return (h % 12 || 12) + (m ? ':' + String(m).padStart(2, '0') : '') + (h >= 12 ? 'pm' : 'am');
                                                        },
                                                        toHHMM(px) {
                                                            const m = Math.round(px / (this.ppm * 15)) * 15;
                                                            const t = this.vsm + m;
                                                            return String(Math.floor(t / 60) % 24).padStart(2, '0') + ':' + String(t % 60).padStart(2, '0');
                                                        },
                                                        startMove(e) {
                                                            if (e.button) return;
                                                            this.mode = 'move';
                                                            this.startX = e.pageX;
                                                            this.origLeft = this.left;
                                                            this.dragMoved = false;
                                                            document.body.style.cursor = 'grabbing';
                                                            document.body.style.userSelect = 'none';
                                                        },
                                                        startResize(e) {
                                                            if (e.button) return;
                                                            this.mode = 'resize';
                                                            this.startX = e.pageX;
                                                            this.origWidth = this.width;
                                                            this.dragMoved = false;
                                                            document.body.style.cursor = 'ew-resize';
                                                            document.body.style.userSelect = 'none';
                                                        },
                                                        onMove(e) {
                                                            if (!this.mode) return;
                                                            const dx = e.pageX - this.startX;
                                                            if (Math.abs(dx) > 3) this.dragMoved = true;
                                                            if (this.mode === 'move') this.left = Math.max(0, this.origLeft + dx);
                                                            else this.width = Math.max(15 * this.ppm, this.origWidth + dx);
                                                        },
                                                        onUp() {
                                                            if (!this.mode) return;
                                                            this.mode = null;
                                                            document.body.style.cursor = '';
                                                            document.body.style.userSelect = '';
                                                            if (!this.dragMoved) return;
                                                            const sp = 15 * this.ppm;
                                                            this.left = Math.round(this.left / sp) * sp;
                                                            this.width = Math.max(sp, Math.round(this.width / sp) * sp);
                                                            $wire.moveActivity(this.id, this.toHHMM(this.left), this.toHHMM(this.left + this.width));
                                                        },
                                                        onClick() {
                                                            if (this.dragMoved) return;
                                                            $wire.openEditActivity(this.id);
                                                        }
                                                    }"
                                                        @mousedown.stop="startMove($event)"
                                                        @mousemove.window="onMove($event)" @mouseup.window="onUp()"
                                                        @click.stop="onClick()"
                                                        class="top-2 bottom-2 absolute flex items-stretch rounded overflow-hidden cursor-grab select-none"
                                                        :class="{ 'opacity-70 shadow-lg z-10 scale-y-105': mode }"
                                                        :style="`left:${left}px;width:${width}px;background-color:{{ $block['color'] }}`">
                                                        {{-- Labels (reactive to live width) --}}
                                                        <div
                                                            class="flex flex-col flex-1 justify-center px-1.5 min-w-0 overflow-hidden pointer-events-none">
                                                            <div x-show="width > 90" x-text="`${sFmt()} → ${eFmt()}`"
                                                                class="drop-shadow font-semibold text-[10px] text-white leading-none whitespace-nowrap">
                                                            </div>
                                                            <div x-show="width > 90"
                                                                class="mt-0.5 text-[9px] text-white/80 leading-none whitespace-nowrap">
                                                                {{ $block['label'] }}</div>
                                                            <div x-show="width <= 90 && width > 50" x-text="sFmt()"
                                                                class="drop-shadow font-semibold text-[10px] text-white leading-none whitespace-nowrap">
                                                            </div>
                                                            <div x-show="width <= 50 && width > 20"
                                                                class="font-bold text-[9px] text-white leading-none">
                                                                {{ substr($block['label'], 0, 2) }}</div>
                                                        </div>
                                                        {{-- Right-edge resize handle --}}
                                                        <div x-show="width > 28" @mousedown.stop="startResize($event)"
                                                            class="right-0 absolute inset-y-0 hover:bg-white/30 rounded-r w-2 transition-colors cursor-ew-resize">
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- Fallback: shift window placeholder (non-interactive) --}}
                                                    <div data-block
                                                        class="top-2 bottom-2 absolute flex flex-col justify-center opacity-30 px-1.5 rounded overflow-hidden pointer-events-none"
                                                        style="left: {{ $block['left'] }}px; width: {{ $block['width'] }}px; background-color: {{ $block['color'] }}"
                                                        title="Shift {{ $block['startF'] }} → {{ $block['endF'] }}">
                                                        @if ($block['width'] > 90)
                                                            <div
                                                                class="drop-shadow font-semibold text-[10px] text-white leading-none whitespace-nowrap">
                                                                {{ $block['startF'] }} → {{ $block['endF'] }}
                                                            </div>
                                                        @elseif ($block['width'] > 50)
                                                            <div
                                                                class="drop-shadow font-semibold text-[10px] text-white leading-none whitespace-nowrap">
                                                                {{ $block['startF'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach

                                        </div>
                                    </div>
                                @endforeach
                            @endforeach

                        </div>{{-- /min-width wrapper --}}
                    </div>{{-- /drag-scroll --}}
                </div>{{-- /gantt card --}}
            @endif
        @endif

    </div>

    {{-- ── Activity Modal (add / edit activity block) ──────────────────────── --}}
    <x-modal :show="$showActivityModal" wire-close="showActivityModal" :title="$editingActivityId ? 'Edit Activity' : 'Add Activity Block'" max-width="max-w-sm">

        <div class="space-y-5">

            {{-- Activity Type — colour-swatch picker --}}
            <div class="space-y-2">
                <label class="font-medium text-fg text-sm">Activity Type</label>
                <div class="gap-2 grid grid-cols-3">
                    @foreach ($activityTypeOptions as $opt)
                        <button type="button" wire:click="$set('activityType', '{{ $opt['value'] }}')"
                            @class([
                                'relative flex items-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium transition-all',
                                'ring-2 ring-offset-1 ring-offset-surface border-transparent shadow-sm' =>
                                    $activityType === $opt['value'],
                                'border-surface bg-surface-2 text-fg-muted hover:text-fg hover:border-surface-3' =>
                                    $activityType !== $opt['value'],
                            ])
                            style="{{ $activityType === $opt['value'] ? "background-color:{$opt['color']}18;border-color:{$opt['color']}60;color:{$opt['color']};ring-color:{$opt['color']}" : '' }}">
                            <span class="rounded-full w-2.5 h-2.5 shrink-0"
                                style="background-color: {{ $opt['color'] }}"></span>
                            <span class="truncate">{{ $opt['label'] }}</span>
                            @if ($activityType === $opt['value'])
                                <x-heroicon-s-check-circle class="top-1.5 right-1.5 absolute opacity-80 w-3.5 h-3.5"
                                    style="color: {{ $opt['color'] }}" />
                            @endif
                        </button>
                    @endforeach
                </div>
                @error('activityType')
                    <p class="text-red-400 text-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- Times --}}
            <div class="space-y-2">
                <label class="font-medium text-fg text-sm">Time Range</label>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-10 text-fg-muted text-xs shrink-0">Start</span>
                        <x-time-picker wire-model="activityStart" :value="$activityStart" />
                    </div>
                    @error('activityStart')
                        <p class="text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                    <div class="flex items-center gap-2">
                        <span class="w-10 text-fg-muted text-xs shrink-0">End</span>
                        <x-time-picker wire-model="activityEnd" :value="$activityEnd" />
                    </div>
                    @error('activityEnd')
                        <p class="text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>

        <x-slot:footer>
            @if ($editingActivityId)
                <button type="button" wire:click="deleteActivity({{ $editingActivityId }})"
                    wire:confirm="Delete this activity block?"
                    class="inline-flex items-center gap-1.5 hover:bg-red-500/10 mr-auto px-3 py-2 rounded-md font-medium text-red-400 text-sm transition">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Delete
                </button>
            @endif
            <button type="button" wire:click="$set('showActivityModal', false)"
                class="px-4 py-2 rounded-md font-medium text-fg-muted hover:text-fg text-sm transition">
                Cancel
            </button>
            <button type="button" wire:click="saveActivity" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <span wire:loading wire:target="saveActivity">
                    <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                </span>
                {{ $editingActivityId ? 'Save Changes' : 'Add Block' }}
            </button>
        </x-slot:footer>

    </x-modal>

</div>
