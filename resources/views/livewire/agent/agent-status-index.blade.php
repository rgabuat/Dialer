<div class="space-y-6 p-6" x-data="{ knownUserIds: @js($statuses->pluck('user_id')->values()) }" x-init="window.Echo.private('agent-status')
    .listen('.AgentStatusUpdated', (e) => {
        if ($wire.viewMode === 'grouped') { $wire.$refresh(); return; }
        const filtersActive = ($wire.filterStatus ?? '') !== '' || ($wire.filterGroup ?? '') !== '';
        const isNewUser = !knownUserIds.includes(e.user_id);
        if (isNewUser || filtersActive) {
            if (isNewUser) knownUserIds.push(e.user_id);
            $wire.$refresh();
        } else {
            window.dispatchEvent(new CustomEvent('agent-row-update', { detail: e }));
        }
    });">
    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Agent Status</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Real-time status and availability of all agents.</p>
    </div>
    {{-- STAT STRIP --}}
    <div class="flex flex-wrap items-center gap-2 text-xs shrink-0">
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="text-zinc-500">Total Agents</span>
            <span class="font-semibold text-zinc-200">{{ $totalCount }}</span>
        </div>
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="bg-green-400 rounded-full w-1.5 h-1.5 animate-pulse shrink-0"></span>
            <span class="text-zinc-500">Available</span>
            <span class="font-semibold text-green-400">{{ $availCount }}</span>
        </div>
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="bg-yellow-400 rounded-full w-1.5 h-1.5 shrink-0"></span>
            <span class="text-zinc-500">Unavailable</span>
            <span class="font-semibold text-yellow-400">{{ $unavailCnt }}</span>
        </div>
        @if ($totalCount > 0)
            <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
                <span class="text-zinc-500">Availability</span>
                @php $availPct = round(($availCount / $totalCount) * 100); @endphp
                <span
                    class="font-semibold {{ $availPct >= 70 ? 'text-green-400' : ($availPct >= 40 ? 'text-yellow-400' : 'text-red-400') }}">{{ $availPct }}%</span>
            </div>
        @endif
        <div class="flex items-center gap-2 bg-zinc-900 px-3 py-2 border border-zinc-800 rounded-lg">
            <span class="text-zinc-500">Avg. Offline</span>
            <span class="font-semibold text-zinc-200">{{ $avgLabel ?: '—' }}</span>
        </div>
    </div>

    {{-- AGENT FLEET TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl [overflow:clip]">

        {{-- Table header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-zinc-800 border-b">
            <div class="flex items-center gap-3">
                <h2 class="font-bold text-white text-base">Agent Fleet</h2>
                <span class="flex items-center gap-1.5 font-medium text-green-400 text-xs">
                    <span class="bg-green-400 rounded-full w-1.5 h-1.5 animate-pulse"></span>
                    Live Syncing
                </span>
            </div>

            {{-- Filters + View switcher --}}
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search agent..."
                    class="bg-zinc-900 px-3 py-1.5 border border-zinc-800 focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-white text-sm transition placeholder-zinc-600">

                <x-select-dropdown wire-model="filterStatus" :value="$filterStatus" placeholder="All Statuses"
                    :options="$statusTypes
                        ->map(fn($t) => ['value' => $t->name, 'label' => $t->name, 'color' => $t->color])
                        ->values()
                        ->all()" />

                <x-select-dropdown wire-model="filterGroup" :value="$filterGroup" placeholder="All Groups"
                    :options="$userGroups->map(fn($g) => ['value' => $g->name, 'label' => $g->name])->values()->all()" />

                {{-- View switcher --}}
                <div class="flex items-center gap-0.5 bg-zinc-800 p-1 rounded-lg shrink-0">
                    <button type="button" wire:click="$set('viewMode','table')" title="Table view"
                        class="flex justify-center items-center rounded-md w-7 h-7 transition"
                        :class="'{{ $viewMode }}'
                        === 'table' ? 'bg-zinc-700 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-300'">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round">
                            <line x1="1" y1="3" x2="13" y2="3" />
                            <line x1="1" y1="7" x2="13" y2="7" />
                            <line x1="1" y1="11" x2="13" y2="11" />
                        </svg>
                    </button>
                    <button type="button" wire:click="$set('viewMode','grouped')" title="Grouped view"
                        class="flex justify-center items-center rounded-md w-7 h-7 transition"
                        :class="'{{ $viewMode }}'
                        === 'grouped' ? 'bg-zinc-700 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-300'">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="1" width="5" height="5" rx="1" />
                            <rect x="8" y="1" width="5" height="5" rx="1" />
                            <rect x="1" y="8" width="5" height="5" rx="1" />
                            <rect x="8" y="8" width="5" height="5" rx="1" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        @if ($viewMode === 'table')
            {{-- Table --}}
            <div class="overflow-auto" x-data x-init="const update = () => {
                const pg = $el.nextElementSibling;
                $el.style.maxHeight = (window.innerHeight - $el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
            };
            update();
            window.addEventListener('resize', update);
            $cleanup(() => window.removeEventListener('resize', update));">
                <table class="min-w-full text-white text-sm stagger-rows">
                    <thead class="top-0 z-10 sticky bg-zinc-900">
                        <tr
                            class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                            <th class="px-5 py-3 text-left">Agent</th>
                            <th class="px-5 py-3 text-left">Group</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Available</th>
                            <th class="px-5 py-3 text-left">Since</th>
                            <th class="px-5 py-3 text-left">Elapsed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($statuses as $status)
                            @php $initials = strtoupper(substr($status->user->first_name, 0, 1) . substr($status->user->last_name, 0, 1)); @endphp
                            <tr wire:key="agent-row-{{ $status->user_id }}"
                                class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition"
                                x-data="{
                                    userId: {{ $status->user_id }},
                                    statusName: @js($status->statusType?->name ?? '—'),
                                    statusColor: @js($status->statusType?->color ?? '#a1a1aa'),
                                    isAvailable: @js((bool) $status->statusType?->is_available),
                                    startedAt: @js($status->started_at?->toIso8601String()),
                                    sinceLabel: @js($status->started_at?->format('M d, H:i') ?? '—'),
                                    time: '00:00:00',
                                    interval: null,
                                    start() {
                                        this.tick();
                                        this.interval = setInterval(() => this.tick(), 1000);
                                    },
                                    reset(s) {
                                        clearInterval(this.interval);
                                        this.startedAt = s;
                                        this.time = '00:00:00';
                                        this.start();
                                    },
                                    tick() {
                                        if (!this.startedAt) return;
                                        const d = Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000);
                                        this.time = [Math.floor(d / 3600), Math.floor((d % 3600) / 60), d % 60]
                                            .map(n => String(n).padStart(2, '0')).join(':');
                                    },
                                }" x-init="start()"
                                @agent-row-update.window="
                                if ($event.detail.user_id === userId) {
                                    statusName  = $event.detail.status_name;
                                    statusColor = $event.detail.status_color;
                                    isAvailable = $event.detail.is_available;
                                    sinceLabel  = new Date($event.detail.started_at).toLocaleString('en-US', { month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12:false });
                                    reset($event.detail.started_at);
                                }
                            ">
                                {{-- Agent --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="inline-flex justify-center items-center bg-zinc-700 rounded-full w-9 h-9 font-bold text-white text-xs shrink-0">
                                            {{ $initials }}
                                        </span>
                                        <div>
                                            <div class="font-semibold text-white text-sm">
                                                {{ $status->user->first_name }}
                                                {{ $status->user->last_name }}</div>
                                            <div class="text-zinc-500 text-xs">{{ $status->user->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Group --}}
                                <td class="px-5 py-4 text-zinc-400 text-sm">
                                    {{ $status->user->userGroup?->name ?? '—' }}
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide"
                                        :style="`background-color: ${statusColor}25; color: ${statusColor}`">
                                        <span class="rounded-full w-1.5 h-1.5"
                                            :style="`background-color: ${statusColor}`"></span>
                                        <span x-text="statusName"></span>
                                    </span>
                                </td>

                                {{-- Available --}}
                                <td class="px-5 py-4">
                                    <span x-show="isAvailable" class="font-bold text-green-400 text-sm">YES</span>
                                    <span x-show="!isAvailable" class="text-zinc-500 text-sm">NO</span>
                                </td>

                                {{-- Since --}}
                                <td class="px-5 py-4 text-zinc-400 text-sm" x-text="sinceLabel"></td>

                                {{-- Elapsed --}}
                                <td class="px-5 py-4 font-mono text-zinc-300 text-sm" x-text="time"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-zinc-500 text-center italic">
                                    No agent statuses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <x-table-pagination :paginator="$statuses" label="agents" />
        @endif

        @if ($viewMode === 'grouped')
            {{-- Grouped View --}}
            <div class="overflow-auto" x-data x-init="const update = () => {
                $el.style.maxHeight = (window.innerHeight - $el.getBoundingClientRect().top - 8) + 'px';
            };
            update();
            window.addEventListener('resize', update);
            $cleanup(() => window.removeEventListener('resize', update));">
                @forelse ($grouped as $g)
                    {{-- Group header --}}
                    <div
                        class="top-0 z-10 sticky flex justify-between items-center bg-[#0d0f13] px-4 py-2 border-zinc-800/80 border-b">
                        <div class="flex items-center gap-2">
                            <span
                                class="font-bold text-zinc-400 text-xs uppercase tracking-widest">{{ $g['name'] }}</span>
                            <span class="font-medium text-[10px] text-zinc-600">{{ $g['count'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="bg-green-500/10 px-2 py-0.5 rounded font-semibold text-[10px] text-green-400">{{ $g['availCount'] }}
                                available</span>
                            @if ($g['count'] > 0)
                                @php $grpPct = round(($g['availCount'] / $g['count']) * 100); @endphp
                                <span
                                    class="font-medium text-[10px] {{ $grpPct >= 70 ? 'text-green-500' : ($grpPct >= 40 ? 'text-yellow-500' : 'text-red-500') }}">{{ $grpPct }}%</span>
                            @endif
                        </div>
                    </div>

                    {{-- Agent rows --}}
                    @foreach ($g['agents'] as $status)
                        @php $initials = strtoupper(substr($status->user->first_name, 0, 1) . substr($status->user->last_name, 0, 1)); @endphp
                        <div wire:key="grouped-agent-{{ $status->user_id }}"
                            class="group flex items-center gap-3 hover:bg-white/[0.02] px-4 py-3 border-zinc-800/40 border-b transition gantt-row-animate"
                            x-data="{
                                userId: {{ $status->user_id }},
                                statusName: @js($status->statusType?->name ?? '—'),
                                statusColor: @js($status->statusType?->color ?? '#a1a1aa'),
                                isAvailable: @js((bool) $status->statusType?->is_available),
                                startedAt: @js($status->started_at?->toIso8601String()),
                                sinceLabel: @js($status->started_at?->format('g:ia') ?? '—'),
                                time: '00:00:00',
                                start() { this.tick();
                                    this.interval = setInterval(() => this.tick(), 1000); },
                                tick() {
                                    if (!this.startedAt) return;
                                    const d = Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000);
                                    this.time = [Math.floor(d / 3600), Math.floor((d % 3600) / 60), d % 60].map(n => String(n).padStart(2, '0')).join(':');
                                },
                            }" x-init="start()"
                            @agent-row-update.window="
                            if ($event.detail.user_id === userId) {
                                statusName  = $event.detail.status_name;
                                statusColor = $event.detail.status_color;
                                isAvailable = $event.detail.is_available;
                                sinceLabel  = new Date($event.detail.started_at).toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit', hour12:true });
                            }
                        ">
                            {{-- Colored avatar --}}
                            <div class="flex justify-center items-center rounded-full w-8 h-8 font-bold text-[11px] uppercase transition-all select-none shrink-0"
                                :style="`background-color:${statusColor}22; color:${statusColor}; border:1px solid ${statusColor}44`">
                                {{ $initials }}
                            </div>
                            {{-- Name + status --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span
                                        class="font-medium text-zinc-200 text-sm truncate">{{ $status->user->first_name }}
                                        {{ $status->user->last_name }}</span>
                                    <span
                                        class="px-1.5 py-0.5 rounded font-semibold text-[10px] transition-all shrink-0"
                                        :style="`background-color:${statusColor}22; color:${statusColor}`"
                                        x-text="statusName"></span>
                                    <span x-show="isAvailable"
                                        class="bg-green-500/10 px-1.5 py-0.5 rounded font-semibold text-[10px] text-green-400 shrink-0">
                                        Available
                                    </span>
                                </div>
                                <div class="mt-0.5 text-[11px] text-zinc-600 truncate">{{ $status->user->email }}
                                </div>
                            </div>
                            {{-- Timer + Since --}}
                            <div class="text-right shrink-0">
                                <div class="font-mono text-xs transition-colors"
                                    :class="isAvailable ? 'text-green-400' : 'text-zinc-500'" x-text="time"></div>
                                <div class="mt-0.5 text-[10px] text-zinc-600" x-text="sinceLabel"></div>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="flex justify-center items-center py-16 text-zinc-500 text-sm italic">
                        No agents found.
                    </div>
                @endforelse
            </div>
        @endif

    </div>

</div>
