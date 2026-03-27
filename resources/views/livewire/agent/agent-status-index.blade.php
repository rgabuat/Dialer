<div class="space-y-6 p-6" x-data="{ knownUserIds: @js($statuses->pluck('user_id')->values()) }" x-init="window.Echo.private('agent-status')
    .listen('.AgentStatusUpdated', (e) => {
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
    {{-- STAT CARDS --}}
    <div class="gap-4 grid grid-cols-2 lg:grid-cols-4 stagger-children">

        {{-- Total Agents --}}
        <div class="flex flex-col gap-3 bg-zinc-900 p-5 border border-zinc-800 rounded-xl card-hover">
            <div class="flex justify-between items-start">
                <span class="font-semibold text-zinc-500 text-xs uppercase tracking-widest">Total Agents</span>
                <x-heroicon-o-user class="w-5 h-5 text-zinc-700" />
            </div>
            <div class="font-black text-white text-3xl">{{ number_format($totalCount) }}</div>
        </div>

        {{-- Available --}}
        <div class="flex flex-col gap-3 bg-zinc-900 p-5 border border-zinc-800 rounded-xl card-hover">
            <div class="flex justify-between items-start">
                <span class="font-semibold text-zinc-500 text-xs uppercase tracking-widest">Available</span>
                <x-heroicon-o-check-circle class="w-5 h-5 text-zinc-700" />
            </div>
            <div class="font-black text-green-400 text-3xl">{{ number_format($availCount) }}</div>
            @if ($totalCount > 0)
                <div class="space-y-1">
                    <div class="bg-zinc-800 rounded-full w-full h-1">
                        <div class="bg-green-500 rounded-full h-1"
                            style="width: {{ round(($availCount / $totalCount) * 100) }}%"></div>
                    </div>
                    <span class="text-zinc-500 text-xs">{{ round(($availCount / $totalCount) * 100) }}%</span>
                </div>
            @endif
        </div>

        {{-- Unavailable --}}
        <div class="flex flex-col gap-3 bg-zinc-900 p-5 border border-zinc-800 rounded-xl card-hover">
            <div class="flex justify-between items-start">
                <span class="font-semibold text-zinc-500 text-xs uppercase tracking-widest">Unavailable</span>
                <x-heroicon-o-clock class="w-5 h-5 text-zinc-700" />
            </div>
            <div class="font-black text-yellow-400 text-3xl">{{ number_format($unavailCnt) }}</div>
            @if ($totalCount > 0)
                <div class="space-y-1">
                    <div class="bg-zinc-800 rounded-full w-full h-1">
                        <div class="bg-yellow-500 rounded-full h-1"
                            style="width: {{ round(($unavailCnt / $totalCount) * 100) }}%"></div>
                    </div>
                    <span class="text-zinc-500 text-xs">{{ round(($unavailCnt / $totalCount) * 100) }}%</span>
                </div>
            @endif
        </div>

        {{-- Avg. Time Offline --}}
        <div class="flex flex-col gap-3 bg-zinc-900 p-5 border border-zinc-800 rounded-xl card-hover">
            <div class="flex justify-between items-start">
                <span class="font-semibold text-zinc-500 text-xs uppercase tracking-widest">Avg. Offline</span>
                <x-heroicon-o-no-symbol class="w-5 h-5 text-zinc-700" />
            </div>
            <div class="font-black text-white text-3xl">{{ $avgLabel ?: '—' }}</div>
        </div>

    </div>

    {{-- AGENT FLEET TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">

        {{-- Table header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-zinc-800 border-b">
            <div class="flex items-center gap-3">
                <h2 class="font-bold text-white text-base">Agent Fleet</h2>
                <span class="flex items-center gap-1.5 font-medium text-green-400 text-xs">
                    <span class="bg-green-400 rounded-full w-1.5 h-1.5 animate-pulse"></span>
                    Live Syncing
                </span>
            </div>

            {{-- Filters --}}
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search agent..."
                    class="bg-zinc-800 px-3 py-1.5 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 w-44 text-white text-sm placeholder-zinc-500">

                <div class="relative">
                    <select wire:model.live="filterStatus"
                        class="bg-zinc-800 py-1.5 pr-8 pl-3 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 text-zinc-300 text-sm appearance-none cursor-pointer">
                        <option value="">Status: All</option>
                        @foreach ($statusTypes as $type)
                            <option value="{{ $type->name }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <x-heroicon-o-chevron-down
                        class="top-1/2 right-2 absolute w-3.5 h-3.5 text-zinc-500 -translate-y-1/2 pointer-events-none" />
                </div>

                <div class="relative">
                    <select wire:model.live="filterGroup"
                        class="bg-zinc-800 py-1.5 pr-8 pl-3 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 text-zinc-300 text-sm appearance-none cursor-pointer">
                        <option value="">Group: All</option>
                        @foreach ($userGroups as $group)
                            <option value="{{ $group->name }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    <x-heroicon-o-chevron-down
                        class="top-1/2 right-2 absolute w-3.5 h-3.5 text-zinc-500 -translate-y-1/2 pointer-events-none" />
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-white text-sm stagger-rows">
                <thead>
                    <tr class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
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
                            class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition" x-data="{
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
                            }"
                            x-init="start()"
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
                                        <div class="font-semibold text-white text-sm">{{ $status->user->first_name }}
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

        {{-- Pagination footer --}}
        @if ($statuses->hasPages())
            <div class="flex justify-between items-center px-5 py-3 border-zinc-800 border-t">
                <span class="text-zinc-500 text-xs">
                    Showing
                    <span class="font-medium text-white">{{ $statuses->firstItem() }} -
                        {{ $statuses->lastItem() }}</span>
                    of
                    <span class="font-medium text-white">{{ number_format($statuses->total()) }}</span>
                    agents
                </span>
                {{ $statuses->links() }}
            </div>
        @else
            <div class="px-5 py-3 border-zinc-800 border-t">
                <span class="text-zinc-500 text-xs">
                    Showing <span class="font-medium text-white">{{ $statuses->total() }}</span> agents
                </span>
            </div>
        @endif

    </div>

</div>
