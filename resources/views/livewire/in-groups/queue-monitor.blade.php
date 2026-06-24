<div class="flex flex-col h-full p-4 gap-4" x-data="{ connected: false }" x-init="window.Echo.private('call-queue').listen('.CallQueueUpdated', () => { $wire.$refresh(); });
connected = true;">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div>
            <h1 class="font-bold text-fg text-xl leading-none">Call Queue Monitor</h1>
            <p class="mt-1 text-zinc-500 text-xs">Live calls and agent availability scoped to your campaign.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-select-dropdown wire-model="selectedCampaign" :value="$selectedCampaign" placeholder="All Campaigns"
                :options="$campaigns->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->values()->all()" />
            <div
                class="flex items-center gap-1.5 bg-surface border border-surface px-3 py-1.5 rounded-lg text-xs text-zinc-500">
                <span class="rounded-full w-1.5 h-1.5 shrink-0"
                    :class="connected ? 'bg-green-400 animate-pulse' : 'bg-zinc-600'"></span>
                <span x-text="connected ? 'Live' : 'Connecting…'"></span>
            </div>
        </div>
    </div>

    {{-- Stats bar --}}
    @php
        $lm = intdiv($longestSecs, 60);
        $ls = $longestSecs % 60;
        $lwLabel = $longestSecs > 0 ? ($lm > 0 ? "{$lm}m {$ls}s" : "{$ls}s") : '—';
        $lwColor = $longestSecs >= 120 ? 'text-accent-red' : ($longestSecs >= 60 ? 'text-accent-yellow' : 'text-fg-2');
        $lwDot = $longestSecs >= 120 ? 'bg-red-400' : ($longestSecs >= 60 ? 'bg-yellow-400' : 'bg-zinc-500');
    @endphp
    <div class="flex flex-wrap items-center gap-2 text-xs shrink-0">
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="bg-yellow-400 rounded-full w-1.5 h-1.5 animate-pulse shrink-0"></span>
            <span class="text-zinc-500">In Queue</span>
            <span class="font-semibold text-accent-yellow">{{ $totalQueued }}</span>
        </div>
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="bg-blue-400 rounded-full w-1.5 h-1.5 animate-pulse shrink-0"></span>
            <span class="text-zinc-500">Active Calls</span>
            <span class="font-semibold text-accent-blue">{{ $totalActive }}</span>
        </div>
        <div class="flex items-center gap-2 bg-surface px-3 py-2 border border-surface rounded-lg">
            <span class="{{ $lwDot }} rounded-full w-1.5 h-1.5 shrink-0"></span>
            <span class="text-zinc-500">Longest Wait</span>
            <span class="font-semibold {{ $lwColor }}">{{ $lwLabel }}</span>
        </div>
    </div>

    {{-- Calls table --}}
    <div class="min-h-0 flex-1 bg-surface border border-surface rounded-xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-surface shrink-0">
            <h2 class="font-semibold text-fg text-sm">Live Calls</h2>
            <div class="flex items-center gap-3 text-xs text-zinc-500">
                @if ($inGroups->isNotEmpty())
                    <span>{{ $inGroups->count() }} queue{{ $inGroups->count() !== 1 ? 's' : '' }}</span>
                    <span class="text-zinc-700">|</span>
                @endif
                <span>{{ $liveCalls->count() }} total</span>
            </div>
        </div>
        <div class="overflow-auto flex-1">
            <table class="min-w-full text-sm">
                <thead class="sticky top-0 bg-surface z-10">
                    <tr class="border-b border-surface text-zinc-500 text-xs uppercase tracking-wider font-semibold">
                        <th class="px-4 py-2.5 text-left w-24">Status</th>
                        <th class="px-4 py-2.5 text-left">Caller</th>
                        <th class="px-4 py-2.5 text-left">CRM Record</th>
                        <th class="px-4 py-2.5 text-left">Location</th>
                        <th class="px-4 py-2.5 text-left">Agent</th>
                        <th class="px-4 py-2.5 text-right w-20">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($liveCalls as $call)
                        @php
                            $secs = now()->diffInSeconds($call->started_at);
                            $min = intdiv($secs, 60);
                            $sec = $secs % 60;
                            $timeLabel = $min > 0 ? "{$min}m {$sec}s" : "{$sec}s";
                            $isQueued = $call->status === 'queued';
                            $timeColor = $isQueued
                                ? ($secs >= 120
                                    ? 'text-accent-red font-bold'
                                    : ($secs >= 60
                                        ? 'text-accent-yellow font-semibold'
                                        : 'text-fg-muted'))
                                : 'text-accent-blue font-semibold';
                        @endphp
                        <tr class="border-b border-surface last:border-0 hover:bg-hover transition">
                            <td class="px-4 py-2.5">
                                @if ($isQueued)
                                    <span
                                        class="inline-flex items-center gap-1 bg-yellow-500/10 text-accent-yellow text-xs font-medium px-2 py-0.5 rounded-full">
                                        <span class="bg-yellow-400 rounded-full w-1 h-1 animate-pulse"></span>Waiting
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 bg-blue-500/10 text-accent-blue text-xs font-medium px-2 py-0.5 rounded-full">
                                        <span class="bg-blue-400 rounded-full w-1 h-1 animate-pulse"></span>Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-fg leading-none">
                                    {{ $call->caller_name ?: $call->contact_name ?: 'Unknown Caller' }}
                                </div>
                                @if ($call->contact_phone)
                                    <div class="text-zinc-500 text-xs mt-0.5 tabular-nums">{{ $call->contact_phone }}
                                    </div>
                                @endif
                            </td>
                            {{-- CRM Record --}}
                            <td class="px-4 py-2.5 max-w-xs">
                                @if ($call->lead)
                                    @php $note = $leadNotes[$call->lead_id] ?? null; @endphp
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span
                                                class="inline-flex items-center gap-1 bg-green-500/10 text-accent-green text-[10px] font-semibold px-1.5 py-0.5 rounded shrink-0">Known</span>
                                            <span
                                                class="text-fg text-xs font-medium leading-none">{{ $call->lead->first_name }}
                                                {{ $call->lead->last_name }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-x-3 gap-y-0.5">
                                            @if ($call->lead->email)
                                                <span class="text-zinc-500 text-[10px]">{{ $call->lead->email }}</span>
                                            @endif
                                            @if ($call->lead->pipeline_stage)
                                                <span
                                                    class="text-zinc-500 text-[10px] capitalize">{{ str_replace('_', ' ', $call->lead->pipeline_stage) }}</span>
                                            @endif
                                            @if ($call->lead->source)
                                                <span
                                                    class="text-zinc-500 text-[10px]">{{ $call->lead->source }}</span>
                                            @endif
                                            @if ($call->lead->call_count)
                                                <span class="text-zinc-500 text-[10px]">{{ $call->lead->call_count }}
                                                    prior call{{ $call->lead->call_count !== 1 ? 's' : '' }}</span>
                                            @endif
                                        </div>
                                        @if ($note)
                                            <div
                                                class="flex items-start gap-1 mt-1 bg-surface-2 border border-surface rounded px-2 py-1">
                                                <svg class="w-3 h-3 text-zinc-500 shrink-0 mt-px" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                                </svg>
                                                <span
                                                    class="text-zinc-400 text-[10px] leading-snug line-clamp-2">{{ $note->content }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 bg-zinc-500/10 text-zinc-400 text-[10px] font-semibold px-1.5 py-0.5 rounded">New
                                        / Unknown</span>
                                @endif
                            </td>
                            {{-- Location --}}
                            <td class="px-4 py-2.5">
                                @php
                                    $locationParts = array_filter([
                                        $call->caller_city,
                                        $call->caller_state,
                                        $call->caller_country,
                                    ]);
                                @endphp
                                @if ($locationParts)
                                    <div class="text-fg-muted text-xs">{{ implode(', ', $locationParts) }}</div>
                                @else
                                    <span class="text-zinc-600 text-xs">—</span>
                                @endif

                            <td class="px-4 py-2.5">
                                @if ($call->assignedAgent)
                                    <div class="flex items-center gap-1.5">
                                        <div
                                            class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center text-accent-blue font-bold text-[10px] shrink-0">
                                            {{ strtoupper(substr($call->assignedAgent->first_name, 0, 1)) }}{{ strtoupper(substr($call->assignedAgent->last_name, 0, 1)) }}
                                        </div>
                                        <span
                                            class="text-fg-muted text-xs truncate">{{ $call->assignedAgent->first_name }}
                                            {{ $call->assignedAgent->last_name }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-600 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-xs {{ $timeColor }}">
                                {{ $timeLabel }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-zinc-500 text-sm">No live calls right
                                now.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
