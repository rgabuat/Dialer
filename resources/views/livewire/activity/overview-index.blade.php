<div class="space-y-6 p-6">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Overview</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">A live summary of key metrics for today.</p>
    </div>

    {{-- GRID: 2 columns on large screens --}}
    <div class="gap-4 grid grid-cols-1 lg:grid-cols-2 stagger-children">

        {{-- ──────────────── LEFT COLUMN ──────────────── --}}

        {{-- Volume --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-fg-muted text-sm uppercase tracking-wider">Volume</h3>
            <div class="gap-4 grid grid-cols-3 stagger-children">
                <div>
                    <div x-data="countUp({{ $offered }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Calls Offered</div>
                </div>
                <div>
                    <div x-data="countUp({{ $callbacksPending }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Callbacks Remaining</div>
                </div>
                <div>
                    <div x-data="countUpTime({{ $avgHandleMins }}, {{ $avgHandleRem }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Average Handling Time</div>
                </div>
            </div>
        </div>

        {{-- Service --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-fg-muted text-sm uppercase tracking-wider">Service</h3>
            <div class="gap-4 grid grid-cols-2 stagger-children">
                <div class="flex items-center gap-3">
                    <x-overview-donut :value="$answerRate" color="#eab308" size="52" />
                    <div>
                        <div class="font-black text-2xl text-accent-yellow">{{ $answerRate }}%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Answer Rate</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-overview-donut :value="min($abandonRate * 10, 100)" color="#ef4444" size="52" />
                    <div>
                        <div class="font-black text-2xl text-accent-red">{{ $abandonRate }}%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Abandon Rate</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Handled --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-fg-muted text-sm uppercase tracking-wider">Handled</h3>
            <div class="gap-4 grid grid-cols-4 stagger-children">
                <div>
                    <div x-data="countUp({{ $totalHandled }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Total</div>
                </div>
                <div>
                    <div x-data="countUp({{ $inboundHandled }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Inbounds</div>
                </div>
                <div>
                    <div x-data="countUp({{ $outboundHandled }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Outbounds</div>
                </div>
                <div>
                    <div x-data="countUp({{ $callbackHandled }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Callbacks</div>
                </div>
            </div>
        </div>

        {{-- Outcomes --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-fg-muted text-sm uppercase tracking-wider">Outcomes</h3>
            <div class="gap-4 grid grid-cols-2 stagger-children">
                <div class="flex items-center gap-3">
                    <x-overview-donut :value="$dispositionRate" color="#ec4899" size="52" />
                    <div>
                        <div class="font-black text-2xl text-accent-pink">{{ $dispositionRate }}%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Disposition Rate</div>
                    </div>
                </div>
                <div>
                    <div x-data="countUp({{ $inProgress }})" x-text="val" class="font-black text-3xl text-accent-teal"></div>
                    <div class="mt-1 text-zinc-500 text-xs">In Progress</div>
                </div>
            </div>
        </div>

        {{-- Abandoned Calls --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-fg-muted text-sm uppercase tracking-wider">Abandoned Calls</h3>
            <div class="gap-4 grid grid-cols-3 stagger-children">
                <div>
                    <div x-data="countUp({{ $abandonedCount }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Abandoned Calls</div>
                </div>
                <div>
                    <div x-data="countUpTime({{ $avgAbandonMins }}, {{ $avgAbandonRem }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Average Time To Abandon</div>
                </div>
                <div class="flex items-center gap-3">
                    <x-overview-donut :value="min($abandonRate * 10, 100)" color="#71717a" size="52" />
                    <div>
                        <div class="font-black text-fg-muted text-2xl">{{ $abandonRate }}%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Abandoned Call Rate</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Agents --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-fg-muted text-sm uppercase tracking-wider">Agents</h3>
            <div class="gap-4 grid grid-cols-3 stagger-children">
                <div>
                    <div x-data="countUp({{ $agentsOnline }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Online</div>
                </div>
                <div>
                    <div x-data="countUp({{ $agentsAvailable }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">Available</div>
                </div>
                <div>
                    <div x-data="countUp({{ $agentsOnBreak }})" x-text="val" class="font-black text-3xl text-accent-blue"></div>
                    <div class="mt-1 text-zinc-500 text-xs">On Break</div>
                </div>
            </div>
        </div>

    </div>

</div>
