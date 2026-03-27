<div class="p-6"
    x-data="{
        knownUserIds: @js($groups->flatten()->pluck('user_id')->values()),
        knownGroups:  @js($groups->keys()->values()),
    }"
    x-init="
        window.Echo.private('agent-status')
            .listen('.AgentStatusUpdated', (e) => {
                const filterActive = ($wire.filterStatus ?? '') !== '';
                const isNewUser    = !knownUserIds.includes(e.user_id);
                const isNewGroup   = !knownGroups.includes(e.user_group_name);

                if (isNewUser || isNewGroup || filterActive) {
                    if (isNewUser)  knownUserIds.push(e.user_id);
                    if (isNewGroup) knownGroups.push(e.user_group_name);
                    $wire.$refresh();
                } else {
                    window.dispatchEvent(new CustomEvent('agent-row-update', { detail: e }));
                }
            });
    ">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-white">Agent Activity Status</h1>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search agent..."
            class="w-full sm:w-64 rounded bg-zinc-900 border border-zinc-800
                   px-3 py-2 text-sm text-white focus:outline-none focus:ring focus:ring-blue-500/20"
        >

        <select
            wire:model.live="filterStatus"
            class="w-full sm:w-48 rounded bg-zinc-900 border border-zinc-800
                   px-3 py-2 text-sm text-white focus:outline-none focus:ring focus:ring-blue-500/20"
        >
            <option value="">All statuses</option>
            @foreach ($statusTypes as $type)
                <option value="{{ $type->name }}">{{ $type->name }}</option>
            @endforeach
        </select>
    </div>

    @forelse ($groups as $groupName => $statuses)
        <div class="mb-8" wire:key="group-{{ Str::slug($groupName) }}">

            {{-- Group header --}}
            <div class="flex items-center gap-3 mb-3">
                <h2 class="text-sm font-semibold text-zinc-300 uppercase tracking-widest">{{ $groupName }}</h2>
                <span class="text-xs text-zinc-500 bg-zinc-800 rounded-full px-2 py-0.5">{{ $statuses->count() }}</span>
                <div class="flex-1 border-t border-zinc-800"></div>
            </div>

            {{-- Group table --}}
            <div class="overflow-x-auto rounded-lg border border-zinc-800">
                <table class="min-w-full text-sm text-white">
                    <thead class="bg-zinc-900 text-zinc-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Agent</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Available</th>
                            <th class="px-4 py-3 text-left">Since</th>
                            <th class="px-4 py-3 text-left">Elapsed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach ($statuses as $status)
                            <tr
                                wire:key="agent-row-{{ $status->user_id }}"
                                class="hover:bg-zinc-900/50 transition"
                                x-data="{
                                    userId: {{ $status->user_id }},
                                    statusName: @js($status->statusType?->name ?? '—'),
                                    statusColor: @js($status->statusType?->color ?? '#a1a1aa'),
                                    isAvailable: @js((bool) $status->statusType?->is_available),
                                    startedAt: @js($status->started_at?->toIso8601String()),
                                    sinceLabel: @js($status->started_at?->format('M d, Y H:i') ?? '—'),
                                    time: '00:00:00',
                                    interval: null,
                                    start() {
                                        this.tick();
                                        this.interval = setInterval(() => this.tick(), 1000);
                                    },
                                    reset(newStartedAt) {
                                        clearInterval(this.interval);
                                        this.startedAt = newStartedAt;
                                        this.time = '00:00:00';
                                        this.start();
                                    },
                                    tick() {
                                        if (!this.startedAt) return;
                                        const diff = Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000);
                                        const h = String(Math.floor(diff / 3600)).padStart(2, '0');
                                        const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
                                        const s = String(diff % 60).padStart(2, '0');
                                        this.time = `${h}:${m}:${s}`;
                                    },
                                }"
                                x-init="start()"
                                @agent-row-update.window="
                                    if ($event.detail.user_id === userId) {
                                        statusName  = $event.detail.status_name;
                                        statusColor = $event.detail.status_color;
                                        isAvailable = $event.detail.is_available;
                                        sinceLabel  = new Date($event.detail.started_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false });
                                        reset($event.detail.started_at);
                                    }
                                "
                            >
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $status->user->first_name }} {{ $status->user->last_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $status->user->email }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium"
                                        :style="`background-color: ${statusColor}22; color: ${statusColor}`">
                                        <span class="w-1.5 h-1.5 rounded-full inline-block" :style="`background-color: ${statusColor}`"></span>
                                        <span x-text="statusName"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span x-show="isAvailable" class="text-green-400 text-xs font-medium">Yes</span>
                                    <span x-show="!isAvailable" class="text-zinc-500 text-xs">No</span>
                                </td>
                                <td class="px-4 py-3 text-zinc-400 text-xs" x-text="sinceLabel"></td>
                                <td class="px-4 py-3 text-zinc-400 font-mono text-xs" x-text="time"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-zinc-800 px-4 py-12 text-center text-zinc-500 italic">
            No agent statuses found.
        </div>
    @endforelse

</div>
