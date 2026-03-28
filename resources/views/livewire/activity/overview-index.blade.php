<div class="space-y-6 p-6">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Overview</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">A live summary of key metrics for today.</p>
    </div>

    {{-- GRID: 2 columns on large screens --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 stagger-children">

        {{-- ──────────────── LEFT COLUMN ──────────────── --}}

        {{-- Volume --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-zinc-400 text-sm uppercase tracking-wider">Volume</h3>
            <div class="gap-4 grid grid-cols-3">
                <div>
                    <div class="font-black text-blue-400 text-3xl">661</div>
                    <div class="mt-1 text-zinc-500 text-xs">Calls Offered</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">0</div>
                    <div class="mt-1 text-zinc-500 text-xs">Callbacks Remaining</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">05:20</div>
                    <div class="mt-1 text-zinc-500 text-xs">Average Handling Time</div>
                </div>
            </div>
        </div>

        {{-- Service --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-zinc-400 text-sm uppercase tracking-wider">Service</h3>
            <div class="gap-4 grid grid-cols-2">
                <div>
                    <div class="font-black text-blue-400 text-3xl">25:25</div>
                    <div class="mt-1 text-zinc-500 text-xs">Time To Answer</div>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Donut --}}
                    <x-overview-donut value="83" color="#eab308" size="52" />
                    <div>
                        <div class="font-black text-yellow-400 text-2xl">83.13%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Answered In 60</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Handled --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-zinc-400 text-sm uppercase tracking-wider">Handled</h3>
            <div class="gap-4 grid grid-cols-4">
                <div>
                    <div class="font-black text-blue-400 text-3xl">710</div>
                    <div class="mt-1 text-zinc-500 text-xs">Total</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">611</div>
                    <div class="mt-1 text-zinc-500 text-xs">Inbounds</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">99</div>
                    <div class="mt-1 text-zinc-500 text-xs">Outbounds</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">0</div>
                    <div class="mt-1 text-zinc-500 text-xs">Callbacks</div>
                </div>
            </div>
        </div>

        {{-- Sales --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-zinc-400 text-sm uppercase tracking-wider">Sales</h3>
            <div class="gap-4 grid grid-cols-2">
                <div class="flex items-center gap-3">
                    <x-overview-donut value="18" color="#ec4899" size="52" />
                    <div>
                        <div class="font-black text-pink-400 text-2xl">17.89%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Conversion Rate</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-overview-donut value="4" color="#14b8a6" size="52" />
                    <div>
                        <div class="font-black text-teal-400 text-2xl">4.08%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Instant Rental Rate</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Abandoned Calls --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-zinc-400 text-sm uppercase tracking-wider">Abandoned Calls</h3>
            <div class="gap-4 grid grid-cols-3">
                <div>
                    <div class="font-black text-blue-400 text-3xl">1</div>
                    <div class="mt-1 text-zinc-500 text-xs">Abandoned Calls</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">01:43</div>
                    <div class="mt-1 text-zinc-500 text-xs">Average Time To Abandon</div>
                </div>
                <div class="flex items-center gap-3">
                    <x-overview-donut value="1" color="#71717a" size="52" />
                    <div>
                        <div class="font-black text-zinc-400 text-2xl">0.15%</div>
                        <div class="mt-1 text-zinc-500 text-xs">Abandoned Call Rate</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Agents --}}
        <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl card-hover">
            <h3 class="font-semibold text-zinc-400 text-sm uppercase tracking-wider">Agents</h3>
            <div class="gap-4 grid grid-cols-3">
                <div>
                    <div class="font-black text-blue-400 text-3xl">48</div>
                    <div class="mt-1 text-zinc-500 text-xs">Online</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">11</div>
                    <div class="mt-1 text-zinc-500 text-xs">Available</div>
                </div>
                <div>
                    <div class="font-black text-blue-400 text-3xl">6</div>
                    <div class="mt-1 text-zinc-500 text-xs">After Call Work</div>
                </div>
            </div>
        </div>

    </div>

</div>
