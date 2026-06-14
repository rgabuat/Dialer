<div class="flex flex-col gap-0 h-full">
@php
    $rangeLabels = [
        'today'      => 'Today',
        'yesterday'  => 'Yesterday',
        'last_7'     => 'Last 7 Days',
        'last_30'    => 'Last 30 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
    ];

    $completionRate  = $totalConversations ? round($completedConversations / $totalConversations * 100) : 0;
    $abandonRate     = $totalConversations ? round($abandonedConversations / $totalConversations * 100) : 0;
    $conversionRate  = $totalLeads ? round($convertedLeads / $totalLeads * 100) : 0;
    $avgMins         = $avgDuration ? floor($avgDuration / 60) . 'm ' . ($avgDuration % 60) . 's' : '—';

    $channelColors = [
        'voice' => ['bg' => 'bg-blue-500/15', 'text' => 'text-blue-400', 'bar' => 'bg-blue-500'],
        'sms'   => ['bg' => 'bg-fuchsia-500/15', 'text' => 'text-fuchsia-400', 'bar' => 'bg-fuchsia-500'],
        'email' => ['bg' => 'bg-orange-500/15', 'text' => 'text-orange-400', 'bar' => 'bg-orange-500'],
        'chat'  => ['bg' => 'bg-teal-500/15', 'text' => 'text-teal-400', 'bar' => 'bg-teal-500'],
    ];

    $stageColors = [
        'interested'           => 'bg-yellow-400/20 text-yellow-300',
        'converted'            => 'bg-green-400/20 text-green-300',
        'expired'              => 'bg-surface-3 text-fg-muted',
        'no_longer_interested' => 'bg-surface-3 text-fg-muted',
    ];
    $stageLabels = [
        'interested'           => 'Interested',
        'converted'            => 'Converted',
        'expired'              => 'Expired',
        'no_longer_interested' => 'No Longer Interested',
    ];

    $typeLabels = [
        'quote'       => 'Quote',
        'reservation' => 'Reservation',
        'waitlist'    => 'Waitlist',
        'rental'      => 'Rental',
    ];
    $typeColors = [
        'quote'       => 'bg-blue-500/15 text-blue-300',
        'reservation' => 'bg-fuchsia-500/15 text-fuchsia-300',
        'waitlist'    => 'bg-yellow-500/15 text-yellow-300',
        'rental'      => 'bg-green-500/15 text-green-300',
    ];

    $maxChannel    = $byChannel->max('total') ?: 1;
    $maxCampaign   = $byCampaign->max('total') ?: 1;
    $maxDisposition = $byDisposition->max('total') ?: 1;
    $maxAgent      = $byAgent->max('total') ?: 1;
    $maxLeadType   = $leadsByType->max('total') ?: 1;
    $maxLeadStage  = $leadsByStage->max('total') ?: 1;
    $maxLeadStore  = $leadsByStore->max('total') ?: 1;
    $maxLeadAgent  = $leadsByAgent->max('total') ?: 1;
@endphp

    {{-- ── Page Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-center gap-4 px-6 pt-5 pb-4 shrink-0">
        <div>
            <h1 class="font-bold text-fg text-xl leading-tight">Reports</h1>
            <p class="mt-0.5 text-fg-muted text-sm">Analytics and performance insights.</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Export button --}}
            <button wire:click="export" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-surface-3 disabled:opacity-60 px-3 py-1.5 border border-surface rounded-lg font-semibold text-fg text-xs transition">
                <span wire:loading.remove wire:target="export">
                    <x-heroicon-o-arrow-down-tray class="inline -mt-0.5 w-3.5 h-3.5 text-fg-muted" />
                </span>
                <span wire:loading wire:target="export">
                    <x-heroicon-o-arrow-path class="inline -mt-0.5 w-3.5 h-3.5 text-fg-muted animate-spin" />
                </span>
                <span wire:loading.remove wire:target="export">Export CSV</span>
                <span wire:loading wire:target="export">Exporting…</span>
            </button>

            {{-- Date range selector --}}
            <div class="flex items-center gap-1 bg-surface-2 p-0.5 border border-surface rounded-lg">
                @foreach ($rangeLabels as $key => $label)
                    <button wire:click="$set('range', '{{ $key }}')"
                        class="px-3 py-1.5 rounded-md text-xs font-semibold transition whitespace-nowrap
                            {{ $range === $key ? 'bg-surface-3 text-fg shadow-sm' : 'text-fg-muted hover:text-fg hover:bg-surface-3/50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Tab Navigation ─────────────────────────────────────────────────── --}}
    <div class="flex items-center px-6 border-surface border-b shrink-0">
        @foreach (['overview' => 'Overview', 'conversations' => 'Conversations', 'leads' => 'Leads', 'agents' => 'Agents', 'campaigns' => 'Campaigns'] as $key => $label)
            <button wire:click="$set('tab', '{{ $key }}')"
                class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition
                    {{ $tab === $key ? 'border-fuchsia-500 text-fg' : 'border-transparent text-fg-muted hover:text-fg' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ── Content ─────────────────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto">

        {{-- ═══════════════════════════════════════════════════════════════════
             OVERVIEW TAB
        ════════════════════════════════════════════════════════════════════ --}}
        @if ($tab === 'overview')
        @php
            $ovDailyLabels = json_encode(array_map(fn($d) => \Carbon\Carbon::parse($d)->format('j M'), array_keys($dailyVolume)));
            $ovDailyData   = json_encode(array_values($dailyVolume));

            $chLabels = json_encode($byChannel->pluck('channel')->map(fn($c) => ucfirst($c))->values()->all());
            $chData   = json_encode($byChannel->pluck('total')->values()->all());
            $chBg     = json_encode($byChannel->pluck('channel')->map(fn($c) => match($c) {
                'voice' => 'rgba(59,130,246,0.8)', 'sms' => 'rgba(168,85,247,0.8)',
                'email' => 'rgba(249,115,22,0.8)', default => 'rgba(20,184,166,0.8)',
            })->values()->all());

            $stLabels = json_encode(['Completed','In Progress','Abandoned','Queued']);
            $stData   = json_encode([
                $byStatus->firstWhere('status','completed')?->total ?? 0,
                $byStatus->firstWhere('status','in_progress')?->total ?? 0,
                $byStatus->firstWhere('status','abandoned')?->total ?? 0,
                $byStatus->firstWhere('status','queued')?->total ?? 0,
            ]);
            $stBg = json_encode(['rgba(74,222,128,0.8)','rgba(168,85,247,0.8)','rgba(248,113,113,0.8)','rgba(250,204,21,0.8)']);

            $ltLabels = json_encode($leadsByType->map(fn($r) => ucfirst($r->lead_type ?? '—'))->values()->all());
            $ltData   = json_encode($leadsByType->pluck('total')->values()->all());
            $ltBg     = json_encode(['rgba(59,130,246,0.8)','rgba(168,85,247,0.8)','rgba(250,204,21,0.8)','rgba(74,222,128,0.8)']);
        @endphp
        @if ($totalConversations === 0 && $totalLeads === 0)
        <div class="flex flex-col justify-center items-center py-24 text-center">
            <div class="flex justify-center items-center bg-surface-2 mb-5 rounded-2xl w-16 h-16">
                <x-heroicon-o-chart-bar class="w-8 h-8 text-fg-muted/40" />
            </div>
            <p class="font-semibold text-fg text-sm">No data for this period</p>
            <p class="mt-1 text-fg-muted text-xs">Try a different date range to see results.</p>
        </div>
        @else
        <div class="space-y-6 p-6">

            {{-- KPI cards --}}
            <div class="gap-3 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
                <div class="space-y-1 bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Total Conversations</p>
                    <p class="font-bold text-fg text-2xl leading-tight">{{ number_format($totalConversations) }}</p>
                    <p class="pt-1 text-fg-muted/60 text-xs">In period</p>
                </div>
                <div class="space-y-1 bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Completion Rate</p>
                    <p class="font-bold text-fg text-2xl leading-tight">{{ $completionRate }}%</p>
                    <div class="flex items-center gap-1.5 pt-1">
                        <div class="flex-1 bg-surface-2 rounded-full h-1.5">
                            <div class="bg-green-400 rounded-full h-1.5" style="width: {{ $completionRate }}%"></div>
                        </div>
                        <span class="text-fg-muted/60 text-xs shrink-0">{{ number_format($completedConversations) }}</span>
                    </div>
                </div>
                <div class="space-y-1 bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Avg Handle Time</p>
                    <p class="font-bold text-fg text-2xl leading-tight">{{ $avgMins }}</p>
                    <p class="pt-1 text-fg-muted/60 text-xs">Voice conversations</p>
                </div>
                <div class="space-y-1 bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Total Leads</p>
                    <p class="font-bold text-fg text-2xl leading-tight">{{ number_format($totalLeads) }}</p>
                    <p class="pt-1 text-fg-muted/60 text-xs">
                        @if ($totalLeadValue > 0) ${{ number_format($totalLeadValue) }} value @else Created in period @endif
                    </p>
                </div>
                <div class="space-y-1 bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Conversion Rate</p>
                    <p class="font-bold text-fg text-2xl leading-tight">{{ $conversionRate }}%</p>
                    <div class="flex items-center gap-1.5 pt-1">
                        <div class="flex-1 bg-surface-2 rounded-full h-1.5">
                            <div class="bg-fuchsia-500 rounded-full h-1.5" style="width: {{ $conversionRate }}%"></div>
                        </div>
                        <span class="text-fg-muted/60 text-xs shrink-0">{{ number_format($convertedLeads) }}</span>
                    </div>
                </div>
                <div class="space-y-1 bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Active Agents</p>
                    <p class="font-bold text-fg text-2xl leading-tight">{{ number_format($activeAgents) }}</p>
                    <p class="pt-1 text-fg-muted/60 text-xs">Handled conversations</p>
                </div>
            </div>

            {{-- Daily volume line chart --}}
            <div class="bg-surface p-4 border border-surface rounded-xl"
                x-data="{
                    init() {
                        const ctx = this.$el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: {{ $ovDailyLabels }},
                                datasets: [{
                                    label: 'Conversations',
                                    data: {{ $ovDailyData }},
                                    borderColor: 'rgba(168,85,247,1)',
                                    backgroundColor: 'rgba(168,85,247,0.12)',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    pointBackgroundColor: 'rgba(168,85,247,1)',
                                    fill: true,
                                    tension: 0.4,
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' conversations' } } },
                                scales: {
                                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, maxTicksLimit: 10 } },
                                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true }
                                }
                            }
                        });
                    }
                }">
                <p class="mb-3 font-semibold text-fg text-sm">Daily Conversation Volume</p>
                <div class="h-48"><canvas></canvas></div>
            </div>

            {{-- 2-col: channel doughnut + status doughnut --}}
            <div class="gap-4 grid grid-cols-1 md:grid-cols-2">

                {{-- Conversations by Channel --}}
                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: {{ $chLabels }},
                                    datasets: [{ data: {{ $chData }}, backgroundColor: {{ $chBg }}, borderWidth: 0, hoverOffset: 6 }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                                    plugins: { legend: { position: 'right', labels: { color: '#a1a1aa', font: { size: 12 }, padding: 16, usePointStyle: true } } }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">Conversations by Channel</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

                {{-- Conversation Status --}}
                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: {{ $stLabels }},
                                    datasets: [{ data: {{ $stData }}, backgroundColor: {{ $stBg }}, borderWidth: 0, hoverOffset: 6 }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                                    plugins: { legend: { position: 'right', labels: { color: '#a1a1aa', font: { size: 12 }, padding: 16, usePointStyle: true } } }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">Conversation Status</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

            </div>

            {{-- 2-col: lead type doughnut + pipeline stage bar --}}
            @if ($totalLeads > 0)
            <div class="gap-4 grid grid-cols-1 md:grid-cols-2">

                {{-- Leads by Type --}}
                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: {{ $ltLabels }},
                                    datasets: [{ data: {{ $ltData }}, backgroundColor: {{ $ltBg }}, borderWidth: 0, hoverOffset: 6 }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                                    plugins: { legend: { position: 'right', labels: { color: '#a1a1aa', font: { size: 12 }, padding: 16, usePointStyle: true } } }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">Leads by Type</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

                {{-- Pipeline Stage horizontal bar --}}
                @php
                    $psLabels = json_encode(array_values($stageLabels));
                    $psData   = json_encode(array_map(fn($k) => $leadsByStage->firstWhere('pipeline_stage',$k)?->total ?? 0, array_keys($stageLabels)));
                    $psBg     = json_encode(['rgba(250,204,21,0.8)','rgba(74,222,128,0.8)','rgba(161,161,170,0.5)','rgba(161,161,170,0.5)']);
                @endphp
                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: {{ $psLabels }},
                                    datasets: [{ label: 'Leads', data: {{ $psData }}, backgroundColor: {{ $psBg }}, borderRadius: 4, borderWidth: 0 }]
                                },
                                options: {
                                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true },
                                        y: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 12 } } }
                                    }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">Lead Pipeline Stages</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

            </div>
            @endif

            {{-- Top campaigns table --}}
            @if ($byCampaign->isNotEmpty())
            <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                <div class="flex justify-between items-center px-4 py-3 border-surface border-b">
                    <p class="font-semibold text-fg text-sm">Top Campaigns</p>
                    <button wire:click="$set('tab', 'campaigns')" class="text-fuchsia-400 hover:text-fuchsia-300 text-xs transition">View all →</button>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-surface border-b font-semibold text-fg-muted text-xs">
                            <th class="px-4 py-2.5 text-left">Campaign</th>
                            <th class="px-4 py-2.5 text-right">Conversations</th>
                            <th class="px-4 py-2.5 text-right">Completed</th>
                            <th class="px-4 py-2.5 text-right">Avg Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface">
                        @foreach ($byCampaign->take(5) as $row)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-fg text-xs">{{ $row->campaign?->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-2.5 text-fg text-xs text-right">{{ number_format($row->total) }}</td>
                                <td class="px-4 py-2.5 text-xs text-right">
                                    <span class="text-green-400">{{ number_format($row->completed) }}</span>
                                    <span class="ml-1 text-fg-muted/60">{{ $row->total ? round($row->completed / $row->total * 100) : 0 }}%</span>
                                </td>
                                <td class="px-4 py-2.5 text-fg-muted text-xs text-right">
                                    @if ($row->avg_dur) {{ floor($row->avg_dur / 60) }}m {{ $row->avg_dur % 60 }}s @else — @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
        @endif
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             CONVERSATIONS TAB
        ════════════════════════════════════════════════════════════════════ --}}
        @if ($tab === 'conversations')
        @php
            $cvDailyLabels = json_encode(array_map(fn($d) => \Carbon\Carbon::parse($d)->format('j M'), array_keys($dailyVolume)));
            $cvDailyData   = json_encode(array_values($dailyVolume));

            $cvChLabels = json_encode($byChannel->pluck('channel')->map(fn($c) => ucfirst($c))->values()->all());
            $cvChData   = json_encode($byChannel->pluck('total')->values()->all());
            $cvChBg     = json_encode($byChannel->pluck('channel')->map(fn($c) => match($c) {
                'voice' => 'rgba(59,130,246,0.8)', 'sms' => 'rgba(168,85,247,0.8)',
                'email' => 'rgba(249,115,22,0.8)', default => 'rgba(20,184,166,0.8)',
            })->values()->all());

            $inbound  = $byDirection->firstWhere('direction','inbound')?->total  ?? 0;
            $outbound = $byDirection->firstWhere('direction','outbound')?->total ?? 0;
            $cvDirLabels = json_encode(['Inbound','Outbound']);
            $cvDirData   = json_encode([$inbound, $outbound]);
            $cvDirBg     = json_encode(['rgba(59,130,246,0.8)','rgba(168,85,247,0.8)']);

            $dispLabels = json_encode($byDisposition->map(fn($r) => $r->disposition?->name ?? 'Unknown')->values()->all());
            $dispData   = json_encode($byDisposition->pluck('total')->values()->all());
        @endphp
        @if ($totalConversations === 0)
        <div class="flex flex-col justify-center items-center py-24 text-center">
            <div class="flex justify-center items-center bg-surface-2 mb-5 rounded-2xl w-16 h-16">
                <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-fg-muted/40" />
            </div>
            <p class="font-semibold text-fg text-sm">No conversations for this period</p>
            <p class="mt-1 text-fg-muted text-xs">Try a different date range to see results.</p>
        </div>
        @else
        <div class="space-y-6 p-6">

            {{-- KPIs --}}
            <div class="gap-3 grid grid-cols-2 md:grid-cols-4">
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Total</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ number_format($totalConversations) }}</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Completed</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ number_format($completedConversations) }}</p>
                    <p class="mt-0.5 text-green-400 text-xs">{{ $completionRate }}% rate</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Abandoned</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ number_format($abandonedConversations) }}</p>
                    <p class="mt-0.5 text-red-400 text-xs">{{ $abandonRate }}% rate</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Avg Handle Time</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ $avgMins }}</p>
                </div>
            </div>

            {{-- Daily volume line chart --}}
            <div class="bg-surface p-4 border border-surface rounded-xl"
                x-data="{
                    init() {
                        const ctx = this.$el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: {{ $cvDailyLabels }},
                                datasets: [{
                                    label: 'Conversations',
                                    data: {{ $cvDailyData }},
                                    borderColor: 'rgba(168,85,247,1)',
                                    backgroundColor: 'rgba(168,85,247,0.12)',
                                    borderWidth: 2, pointRadius: 3, pointHoverRadius: 5,
                                    pointBackgroundColor: 'rgba(168,85,247,1)',
                                    fill: true, tension: 0.4,
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, maxTicksLimit: 14 } },
                                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true }
                                }
                            }
                        });
                    }
                }">
                <p class="mb-3 font-semibold text-fg text-sm">Daily Volume</p>
                <div class="h-52"><canvas></canvas></div>
            </div>

            {{-- Channel bar + Direction doughnut --}}
            <div class="gap-4 grid grid-cols-1 md:grid-cols-2">

                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: {{ $cvChLabels }},
                                    datasets: [{ label: 'Conversations', data: {{ $cvChData }}, backgroundColor: {{ $cvChBg }}, borderRadius: 6, borderWidth: 0 }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 12 } } },
                                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true }
                                    }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">By Channel</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: {{ $cvDirLabels }},
                                    datasets: [{ data: {{ $cvDirData }}, backgroundColor: {{ $cvDirBg }}, borderWidth: 0, hoverOffset: 6 }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                                    plugins: { legend: { position: 'right', labels: { color: '#a1a1aa', font: { size: 13 }, padding: 20, usePointStyle: true } } }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">Inbound vs Outbound</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

            </div>

            {{-- Dispositions bar chart --}}
            @if ($byDisposition->isNotEmpty())
            @php
                $dispBg = json_encode(array_fill(0, $byDisposition->count(), 'rgba(168,85,247,0.7)'));
            @endphp
            <div class="bg-surface p-4 border border-surface rounded-xl"
                x-data="{
                    init() {
                        const ctx = this.$el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $dispLabels }},
                                datasets: [{ label: 'Count', data: {{ $dispData }}, backgroundColor: {{ $dispBg }}, borderRadius: 4, borderWidth: 0 }]
                            },
                            options: {
                                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true },
                                    y: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 11 } } }
                                }
                            }
                        });
                    }
                }">
                <div class="flex justify-between items-center mb-3">
                    <p class="font-semibold text-fg text-sm">Dispositions</p>
                    <p class="text-fg-muted/60 text-xs">{{ number_format($byDisposition->sum('total')) }} dispositioned</p>
                </div>
                <div style="height: {{ max(180, $byDisposition->count() * 36) }}px"><canvas></canvas></div>
            </div>
            @endif

        </div>
        @endif
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             LEADS TAB
        ════════════════════════════════════════════════════════════════════ --}}
        @if ($tab === 'leads')
        @php
            $ldTypeLbls = json_encode($leadsByType->map(fn($r) => ucfirst($r->lead_type ?? '—'))->values()->all());
            $ldTypeData = json_encode($leadsByType->pluck('total')->values()->all());
            $ldTypeBg   = json_encode(['rgba(59,130,246,0.8)','rgba(168,85,247,0.8)','rgba(250,204,21,0.8)','rgba(74,222,128,0.8)']);

            $ldStageLbls = json_encode(array_values($stageLabels));
            $ldStageData = json_encode(array_map(fn($k) => $leadsByStage->firstWhere('pipeline_stage',$k)?->total ?? 0, array_keys($stageLabels)));
            $ldStageBg   = json_encode(['rgba(250,204,21,0.8)','rgba(74,222,128,0.8)','rgba(161,161,170,0.5)','rgba(161,161,170,0.5)']);

            $ldStoreLbls = json_encode($leadsByStore->map(fn($r) => $r->store?->name ?? 'Unknown')->values()->all());
            $ldStoreData = json_encode($leadsByStore->pluck('total')->values()->all());

            $ldAgentLbls = json_encode($leadsByAgent->map(fn($r) => $r->creator?->name ?? 'Unknown')->values()->all());
            $ldAgentData = json_encode($leadsByAgent->pluck('total')->values()->all());
        @endphp
        @if ($totalLeads === 0)
        <div class="flex flex-col justify-center items-center py-24 text-center">
            <div class="flex justify-center items-center bg-surface-2 mb-5 rounded-2xl w-16 h-16">
                <x-heroicon-o-user-group class="w-8 h-8 text-fg-muted/40" />
            </div>
            <p class="font-semibold text-fg text-sm">No leads for this period</p>
            <p class="mt-1 text-fg-muted text-xs">Try a different date range to see results.</p>
        </div>
        @else
        <div class="space-y-6 p-6">

            {{-- KPIs --}}
            <div class="gap-3 grid grid-cols-2 md:grid-cols-4">
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Total Leads</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ number_format($totalLeads) }}</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Converted</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ number_format($convertedLeads) }}</p>
                    <p class="mt-0.5 text-fuchsia-400 text-xs">{{ $conversionRate }}% rate</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Pipeline Value</p>
                    <p class="mt-1 font-bold text-fg text-2xl">${{ number_format($totalLeadValue) }}</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Converted Value</p>
                    <p class="mt-1 font-bold text-fg text-2xl">${{ number_format($convertedLeadValue) }}</p>
                </div>
            </div>

            {{-- Type doughnut + stage bar --}}
            <div class="gap-4 grid grid-cols-1 md:grid-cols-2">

                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: {{ $ldTypeLbls }},
                                    datasets: [{ data: {{ $ldTypeData }}, backgroundColor: {{ $ldTypeBg }}, borderWidth: 0, hoverOffset: 6 }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                                    plugins: { legend: { position: 'right', labels: { color: '#a1a1aa', font: { size: 12 }, padding: 16, usePointStyle: true } } }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">By Lead Type</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

                <div class="bg-surface p-4 border border-surface rounded-xl"
                    x-data="{
                        init() {
                            const ctx = this.$el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: {{ $ldStageLbls }},
                                    datasets: [{ label: 'Leads', data: {{ $ldStageData }}, backgroundColor: {{ $ldStageBg }}, borderRadius: 4, borderWidth: 0 }]
                                },
                                options: {
                                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true },
                                        y: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 12 } } }
                                    }
                                }
                            });
                        }
                    }">
                    <p class="mb-3 font-semibold text-fg text-sm">Pipeline Funnel</p>
                    <div class="h-44"><canvas></canvas></div>
                </div>

            </div>

            {{-- Leads by store horizontal bar --}}
            @if ($leadsByStore->isNotEmpty())
            <div class="bg-surface p-4 border border-surface rounded-xl"
                x-data="{
                    init() {
                        const ctx = this.$el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $ldStoreLbls }},
                                datasets: [{ label: 'Leads', data: {{ $ldStoreData }}, backgroundColor: 'rgba(168,85,247,0.7)', borderRadius: 4, borderWidth: 0 }]
                            },
                            options: {
                                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true },
                                    y: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 11 } } }
                                }
                            }
                        });
                    }
                }">
                <p class="mb-3 font-semibold text-fg text-sm">Leads by Store</p>
                <div style="height: {{ max(160, $leadsByStore->count() * 36) }}px"><canvas></canvas></div>
            </div>
            @endif

            {{-- Leads by agent horizontal bar --}}
            @if ($leadsByAgent->isNotEmpty())
            <div class="bg-surface p-4 border border-surface rounded-xl"
                x-data="{
                    init() {
                        const ctx = this.$el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $ldAgentLbls }},
                                datasets: [{ label: 'Leads', data: {{ $ldAgentData }}, backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 4, borderWidth: 0 }]
                            },
                            options: {
                                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true },
                                    y: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 11 } } }
                                }
                            }
                        });
                    }
                }">
                <p class="mb-3 font-semibold text-fg text-sm">Leads by Agent</p>
                <div style="height: {{ max(160, $leadsByAgent->count() * 36) }}px"><canvas></canvas></div>
            </div>
            @endif

        </div>
        @endif
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             AGENTS TAB
        ════════════════════════════════════════════════════════════════════ --}}
        @if ($tab === 'agents')
        @php
            $agLbls  = json_encode($byAgent->map(fn($r) => $r->assignedAgent?->name ?? 'Unassigned')->values()->all());
            $agTotal = json_encode($byAgent->pluck('total')->values()->all());
            $agComp  = json_encode($byAgent->pluck('completed')->values()->all());
        @endphp
        @if ($byAgent->isEmpty())
        <div class="flex flex-col justify-center items-center py-24 text-center">
            <div class="flex justify-center items-center bg-surface-2 mb-5 rounded-2xl w-16 h-16">
                <x-heroicon-o-users class="w-8 h-8 text-fg-muted/40" />
            </div>
            <p class="font-semibold text-fg text-sm">No agent activity for this period</p>
            <p class="mt-1 text-fg-muted text-xs">Try a different date range to see results.</p>
        </div>
        @else
        <div class="space-y-6 p-6">

            {{-- KPIs --}}
            <div class="gap-3 grid grid-cols-2 md:grid-cols-3">
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Active Agents</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ number_format($activeAgents) }}</p>
                    <p class="mt-0.5 text-fg-muted/60 text-xs">Handled at least one conversation</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Total Conversations</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ number_format($totalConversations) }}</p>
                </div>
                <div class="bg-surface p-4 border border-surface rounded-xl">
                    <p class="font-medium text-fg-muted text-xs">Avg Handle Time</p>
                    <p class="mt-1 font-bold text-fg text-2xl">{{ $avgMins }}</p>
                </div>
            </div>

            {{-- Agent grouped bar chart --}}
            @if ($byAgent->isNotEmpty())
            <div class="bg-surface p-4 border border-surface rounded-xl"
                x-data="{
                    init() {
                        const ctx = this.$el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $agLbls }},
                                datasets: [
                                    { label: 'Total', data: {{ $agTotal }}, backgroundColor: 'rgba(168,85,247,0.6)', borderRadius: 4, borderWidth: 0 },
                                    { label: 'Completed', data: {{ $agComp }}, backgroundColor: 'rgba(74,222,128,0.7)', borderRadius: 4, borderWidth: 0 }
                                ]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { position: 'top', labels: { color: '#a1a1aa', font: { size: 12 }, usePointStyle: true, padding: 16 } } },
                                scales: {
                                    x: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 11 } } },
                                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true }
                                }
                            }
                        });
                    }
                }">
                <p class="mb-3 font-semibold text-fg text-sm">Conversations per Agent</p>
                <div style="height: {{ max(200, $byAgent->count() * 50) }}px"><canvas></canvas></div>
            </div>
            @endif

            {{-- Agent performance table --}}
            <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-surface border-b">
                    <p class="font-semibold text-fg text-sm">Agent Performance</p>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-surface border-b font-semibold text-fg-muted text-xs">
                            <th class="px-4 py-2.5 text-left">Agent</th>
                            <th class="px-4 py-2.5 text-right">Conversations</th>
                            <th class="px-4 py-2.5 text-right">Completed</th>
                            <th class="px-4 py-2.5 text-right">Completion%</th>
                            <th class="px-4 py-2.5 text-right">Avg Duration</th>
                            <th class="px-4 py-2.5 text-right">Leads Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface">
                        @forelse ($byAgent as $row)
                            @php
                                $name = $row->assignedAgent?->name ?? 'Unassigned';
                                $initials = strtoupper(substr($name, 0, 1)) . strtoupper(substr(strstr($name, ' ') ?: '', 1, 1));
                                $compPct = $row->total ? round($row->completed / $row->total * 100) : 0;
                                $leadsCount = $leadsPerAgent[$row->assigned_to] ?? 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex justify-center items-center bg-fuchsia-500/30 rounded-full w-8 h-8 font-bold text-fuchsia-200 text-xs shrink-0">{{ $initials ?: '?' }}</div>
                                        <span class="font-semibold text-fg text-xs">{{ $name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-semibold text-fg text-sm text-right">{{ number_format($row->total) }}</td>
                                <td class="px-4 py-3 font-semibold text-green-400 text-xs text-right">{{ number_format($row->completed) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <div class="bg-surface-2 rounded-full w-16 h-1.5">
                                            <div class="bg-green-400 rounded-full h-1.5" style="width: {{ $compPct }}%"></div>
                                        </div>
                                        <span class="w-8 text-fg-muted text-xs">{{ $compPct }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-fg-muted text-xs text-right">
                                    @if ($row->avg_dur) {{ floor($row->avg_dur / 60) }}m {{ $row->avg_dur % 60 }}s @else — @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($leadsCount > 0)
                                        <span class="inline-flex items-center bg-fuchsia-500/15 px-2 py-0.5 rounded-full font-semibold text-fuchsia-300 text-xs">{{ $leadsCount }}</span>
                                    @else
                                        <span class="text-fg-muted text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-fg-muted text-sm text-center">No agent data for this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
        @endif
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             CAMPAIGNS TAB
        ════════════════════════════════════════════════════════════════════ --}}
        @if ($tab === 'campaigns')
        @php
            $cpLbls  = json_encode($byCampaign->map(fn($r) => $r->campaign?->name ?? 'No Campaign')->values()->all());
            $cpTotal = json_encode($byCampaign->pluck('total')->values()->all());
            $cpComp  = json_encode($byCampaign->pluck('completed')->values()->all());
        @endphp
        @if ($byCampaign->isEmpty())
        <div class="flex flex-col justify-center items-center py-24 text-center">
            <div class="flex justify-center items-center bg-surface-2 mb-5 rounded-2xl w-16 h-16">
                <x-heroicon-o-megaphone class="w-8 h-8 text-fg-muted/40" />
            </div>
            <p class="font-semibold text-fg text-sm">No campaign data for this period</p>
            <p class="mt-1 text-fg-muted text-xs">Try a different date range to see results.</p>
        </div>
        @else
        <div class="space-y-6 p-6">

            {{-- Campaign grouped bar chart --}}
            @if ($byCampaign->isNotEmpty())
            <div class="bg-surface p-4 border border-surface rounded-xl"
                x-data="{
                    init() {
                        const ctx = this.$el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ $cpLbls }},
                                datasets: [
                                    { label: 'Total', data: {{ $cpTotal }}, backgroundColor: 'rgba(59,130,246,0.65)', borderRadius: 4, borderWidth: 0 },
                                    { label: 'Completed', data: {{ $cpComp }}, backgroundColor: 'rgba(74,222,128,0.7)', borderRadius: 4, borderWidth: 0 }
                                ]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { position: 'top', labels: { color: '#a1a1aa', font: { size: 12 }, usePointStyle: true, padding: 16 } } },
                                scales: {
                                    x: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { size: 11 } } },
                                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 }, precision: 0 }, beginAtZero: true }
                                }
                            }
                        });
                    }
                }">
                <p class="mb-3 font-semibold text-fg text-sm">Conversations per Campaign</p>
                <div style="height: {{ max(200, $byCampaign->count() * 55) }}px"><canvas></canvas></div>
            </div>
            @endif

            {{-- Campaign table --}}
            <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-surface border-b">
                    <p class="font-semibold text-fg text-sm">Campaign Performance</p>
                    <p class="mt-0.5 text-fg-muted text-xs">{{ $rangeLabels[$range] ?? '' }}</p>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-surface border-b font-semibold text-fg-muted text-xs">
                            <th class="px-4 py-2.5 text-left">Campaign</th>
                            <th class="px-4 py-2.5 text-right">Conversations</th>
                            <th class="px-4 py-2.5 text-right">Completed</th>
                            <th class="px-4 py-2.5 text-right">Completion%</th>
                            <th class="px-4 py-2.5 text-right">Avg Duration</th>
                            <th class="px-4 py-2.5 w-1/4 text-left">Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface">
                        @forelse ($byCampaign as $row)
                            @php $compPct = $row->total ? round($row->completed / $row->total * 100) : 0; @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-fg text-xs">{{ $row->campaign?->name ?? 'No Campaign' }}</td>
                                <td class="px-4 py-3 font-bold text-fg text-sm text-right">{{ number_format($row->total) }}</td>
                                <td class="px-4 py-3 font-semibold text-green-400 text-xs text-right">{{ number_format($row->completed) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="{{ $compPct >= 70 ? 'text-green-400' : ($compPct >= 40 ? 'text-yellow-400' : 'text-red-400') }} text-xs font-semibold">{{ $compPct }}%</span>
                                </td>
                                <td class="px-4 py-3 text-fg-muted text-xs text-right">
                                    @if ($row->avg_dur) {{ floor($row->avg_dur / 60) }}m {{ $row->avg_dur % 60 }}s @else — @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-surface-2 rounded-full h-1.5">
                                            <div class="bg-fuchsia-500/70 rounded-full h-1.5" style="width: {{ round($row->total / $maxCampaign * 100) }}%"></div>
                                        </div>
                                        <span class="w-8 text-fg-muted/60 text-xs text-right">{{ $totalConversations ? round($row->total / $totalConversations * 100) : 0 }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-fg-muted text-sm text-center">No campaign data for this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
        @endif
        @endif

    </div>{{-- /content --}}

</div>
