{{-- ══════════════════════════════════════════════════════════════════
     Agent Control Bar — topbar widget
     Tokens used: bg-surface, border-surface, text-fg, text-fg-muted
     Dark / light mode handled automatically via CSS custom properties.
     ══════════════════════════════════════════════════════════════════ --}}
<div x-data="agentPhone()" @make-call.window="makeCall($event.detail)"
    @agent-status-changed.window="canAcceptCalls = ($event.detail.isAvailable ?? canAcceptCalls)"
    class="flex items-center gap-2">

    {{-- ── CALL CONTROLS pill ──────────────────────────────────── --}}
    <div class="flex items-center gap-1 bg-surface border border-surface rounded-lg px-1.5 py-1">

        {{-- ENABLE CALLING (device not yet initialised) --}}
        <button x-show="!deviceReady" @click="enableCalling" :disabled="!canAcceptCalls"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-150"
            :class="canAcceptCalls
                ?
                'bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm' :
                'bg-surface-2 text-fg-muted cursor-not-allowed opacity-60'">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
            </svg>
            Enable Calling
        </button>

        {{-- READY — no active call: green dot + Dial button --}}
        <template x-if="deviceReady && !hasActiveCall">
            <div class="flex items-center gap-2">
                {{-- Ready indicator --}}
                <span class="inline-flex items-center gap-1.5 text-xs text-fg-muted font-medium">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                    Ready
                </span>
                {{-- Dial button --}}
                <button @click="openDialer"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-surface-2 hover:bg-hover text-fg transition-all duration-150">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                    </svg>
                    Dial
                </button>
            </div>
        </template>

        {{-- ACTIVE CALL: pulsing dot + timer + status + action buttons --}}
        <template x-if="hasActiveCall">
            <div class="flex items-center gap-2">
                {{-- Live pulse indicator --}}
                <span class="relative inline-flex w-2.5 h-2.5">
                    <span
                        class="absolute inline-flex w-full h-full rounded-full bg-red-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-2.5 h-2.5 rounded-full bg-red-500"></span>
                </span>
                {{-- Status + elapsed timer --}}
                <span class="text-xs text-fg font-medium font-mono tracking-wide"
                    x-text="callStatus + (callDuration ? '  ' + callDuration : '')"></span>

                {{-- Mute toggle --}}
                <button @click="toggleMute" :title="isMuted ? 'Unmute' : 'Mute'"
                    :class="isMuted
                        ?
                        'bg-yellow-500/20 text-yellow-400 border-yellow-500/30' :
                        'bg-surface-2 text-fg-muted border-surface hover:bg-hover'"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-md text-xs border transition-all duration-150">
                    <template x-if="!isMuted">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                        </svg>
                    </template>
                    <template x-if="isMuted">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                        </svg>
                    </template>
                </button>

                {{-- Hold / Resume toggle --}}
                <button @click="toggleHold" :title="isOnHold ? 'Resume' : 'Hold'"
                    :class="isOnHold
                        ?
                        'bg-orange-500/20 text-orange-400 border-orange-500/30' :
                        'bg-surface-2 text-fg-muted border-surface hover:bg-hover'"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-md text-xs border transition-all duration-150">
                    <template x-if="!isOnHold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                        </svg>
                    </template>
                    <template x-if="isOnHold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                        </svg>
                    </template>
                </button>

                {{-- Transfer --}}
                <button @click="openTransfer" title="Transfer call"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-md text-xs bg-surface-2 text-fg-muted border border-surface hover:bg-hover transition-all duration-150">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                </button>

                {{-- Hang Up --}}
                <button @click="hangUp"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-red-600 hover:bg-red-500 text-white transition-all duration-150 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 3.75a.75.75 0 0 1 .75.75V8.25a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75V4.5a.75.75 0 0 1 .75-.75h4.5ZM9 15.75a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-.75.75H4.5a.75.75 0 0 1-.75-.75V16.5a.75.75 0 0 1 .75-.75H9ZM3.375 7.5A.375.375 0 0 0 3 7.875v8.25c0 .207.168.375.375.375h17.25A.375.375 0 0 0 21 16.125V7.875A.375.375 0 0 0 20.625 7.5H3.375Z" />
                    </svg>
                    Hang Up
                </button>
            </div>
        </template>

    </div>

    {{-- ── SEPARATOR ───────────────────────────────────────────── --}}
    <div class="w-px h-5 bg-surface-2"></div>

    {{-- ── AGENT STATUS switcher ───────────────────────────────── --}}
    <div class="flex items-center bg-surface border border-surface rounded-lg px-1.5 py-1">
        @livewire('agent.status-switcher')
    </div>

</div>


{{-- ══════════════════════════════════════════════════════════════════
     DIAL MODAL — numeric keypad dialer
     ══════════════════════════════════════════════════════════════════ --}}
<div x-data="{
    open: false,
    digits: '',
    press(d) { this.digits += d; },
    del() { this.digits = this.digits.slice(0, -1); },
    call() {
        if (!this.digits) return;
        this.$dispatch('make-call', this.digits);
        this.open = false;
        this.digits = '';
    }
}"
    x-on:open-dialer.window="open = true; digits = ''; $nextTick(() => $refs.dialDisplay?.focus())" x-show="open"
    x-cloak x-trap.noscroll="open" @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0" @click="open = false"></div>

    {{-- Panel --}}
    <div class="relative z-10 w-full max-w-xs bg-surface border border-surface rounded-2xl shadow-2xl animate-modal-in overflow-hidden"
        @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-surface">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-500/15">
                    <svg class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                    </svg>
                </span>
                <h2 class="text-fg font-semibold text-sm">Outbound Call</h2>
            </div>
            <button @click="open = false"
                class="flex items-center justify-center w-7 h-7 rounded-md text-fg-muted hover:text-fg hover:bg-surface-2 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Number display --}}
        <div class="px-5 pt-4 pb-2">
            <div class="relative flex items-center">
                <input x-ref="dialDisplay" x-model="digits" type="tel" placeholder="+1 (000) 000-0000"
                    @keydown.enter="call()"
                    class="w-full bg-surface-2 border border-surface rounded-xl px-4 py-3 pr-10
                              text-fg text-base font-mono tracking-widest text-center
                              placeholder-fg-muted
                              focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition" />
                {{-- Backspace --}}
                <button @click="del()" x-show="digits.length > 0"
                    class="absolute right-3 text-fg-muted hover:text-fg transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9.75 14.25 12m0 0 2.25 2.25M14.25 12l2.25-2.25M14.25 12 12 14.25m-2.58 4.92-6.374-6.375a1.125 1.125 0 0 1 0-1.59L9.42 4.83c.21-.211.497-.33.795-.33H19.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25h-9.284c-.298 0-.585-.119-.795-.33Z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Keypad --}}
        <div class="px-5 pb-5 pt-2 grid grid-cols-3 gap-2">
            @foreach ([['1', ''], ['2', 'ABC'], ['3', 'DEF'], ['4', 'GHI'], ['5', 'JKL'], ['6', 'MNO'], ['7', 'PQRS'], ['8', 'TUV'], ['9', 'WXYZ'], ['*', ''], ['0', '+'], ['#', '']] as [$digit, $sub])
                <button @click="press('{{ $digit }}')"
                    class="flex flex-col items-center justify-center h-14 rounded-xl
                           bg-surface-2 hover:bg-hover active:scale-95
                           text-fg font-semibold text-base
                           border border-surface
                           transition-all duration-100 select-none">
                    {{ $digit }}
                    @if ($sub)
                        <span
                            class="text-fg-muted text-[9px] font-normal tracking-widest mt-0.5">{{ $sub }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-3 px-5 pb-5">
            <button @click="open = false; digits = ''"
                class="flex-1 py-2.5 rounded-xl bg-surface-2 hover:bg-hover text-fg-3 text-sm font-medium transition">
                Cancel
            </button>
            <button @click="call()" :disabled="!digits"
                :class="digits ? 'bg-green-600 hover:bg-green-500 text-white shadow-sm' :
                    'bg-surface-2 text-fg-muted opacity-50 cursor-not-allowed'"
                class="flex-1 py-2.5 rounded-xl font-semibold text-sm transition inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                </svg>
                Call
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     TRANSFER MODAL — blind (cold) transfer to number or agent
     ══════════════════════════════════════════════════════════════════ --}}
<div x-data="{
    open: false,
    tab: 'number',
    destination: '',
    agents: [],
    loading: false,
    async loadAgents() {
        try {
            const r = await fetch('{{ route('twilio.availableAgents') }}');
            this.agents = await r.json();
        } catch { this.agents = []; }
    }
}" x-on:open-transfer.window="open = true; destination = ''; tab = 'number'; loadAgents()"
    x-show="open" x-cloak x-trap.noscroll="open" @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">

    <div class="absolute inset-0" @click="open = false"></div>

    <div class="relative z-10 w-full max-w-sm bg-surface border border-surface rounded-2xl shadow-2xl animate-modal-in overflow-hidden"
        @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-surface">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500/15">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                </span>
                <h2 class="text-fg font-semibold text-sm">Transfer Call</h2>
            </div>
            <button @click="open = false"
                class="flex items-center justify-center w-7 h-7 rounded-md text-fg-muted hover:text-fg hover:bg-surface-2 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-surface text-sm">
            <button @click="tab='number'"
                :class="tab === 'number' ? 'border-b-2 border-indigo-500 text-fg font-semibold' : 'text-fg-muted hover:text-fg'"
                class="flex-1 py-3 transition">External Number</button>
            <button @click="tab='agent'"
                :class="tab === 'agent' ? 'border-b-2 border-indigo-500 text-fg font-semibold' : 'text-fg-muted hover:text-fg'"
                class="flex-1 py-3 transition">Agent</button>
        </div>

        {{-- Number tab --}}
        <div x-show="tab==='number'" class="px-5 py-4 space-y-3">
            <p class="text-fg-muted text-xs">Enter the number to transfer this call to.</p>
            <input x-model="destination" type="tel" placeholder="+1 (000) 000-0000"
                @keydown.enter="$dispatch('do-transfer', destination); open = false"
                class="w-full bg-surface-2 border border-surface rounded-xl px-4 py-3 text-fg text-sm font-mono tracking-widest text-center placeholder-fg-muted focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition" />
        </div>

        {{-- Agent tab --}}
        <div x-show="tab==='agent'" class="px-5 py-4">
            <p class="text-fg-muted text-xs mb-3">Select an online agent to transfer this call to.</p>
            <div class="space-y-1 max-h-52 overflow-y-auto">
                <template x-if="agents.length === 0">
                    <p class="text-fg-muted text-xs py-3 text-center">No agents currently on Phones status.</p>
                </template>
                <template x-for="agent in agents" :key="agent.id">
                    <button
                        @click="destination = agent.identity; $dispatch('do-transfer', agent.identity); open = false"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg bg-surface-2 hover:bg-hover text-fg text-sm transition">
                        <span
                            class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500/20 text-indigo-400 text-xs font-bold shrink-0"
                            x-text="agent.name.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase()"></span>
                        <span x-text="agent.name" class="truncate"></span>
                        <span class="ml-auto w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 px-5 pb-5">
            <button @click="open = false"
                class="flex-1 py-2.5 rounded-xl bg-surface-2 hover:bg-hover text-fg-muted text-sm font-medium transition">
                Cancel
            </button>
            <button x-show="tab==='number'"
                @click="if(destination){ $dispatch('do-transfer', destination); open = false; }"
                :disabled="!destination"
                :class="destination ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm' :
                    'bg-surface-2 text-fg-muted opacity-50 cursor-not-allowed'"
                class="flex-1 py-2.5 rounded-xl font-semibold text-sm transition inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                Transfer
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     INCOMING CALL MODAL
     ══════════════════════════════════════════════════════════════════ --}}
<div x-data="{ show: false, callerNumber: '' }" @incoming-call.window="show = true; callerNumber = $event.detail" x-show="show" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">

    <div class="relative z-10 w-full max-w-xs bg-surface border border-surface rounded-2xl shadow-2xl animate-modal-in overflow-hidden text-center"
        @click.stop>

        {{-- Animated ring --}}
        <div class="relative flex items-center justify-center pt-8 pb-4">
            {{-- outer ripples --}}
            <span class="absolute w-24 h-24 rounded-full bg-green-500/10 animate-ping"
                style="animation-duration:1.4s"></span>
            <span class="absolute w-16 h-16 rounded-full bg-green-500/15 animate-ping"
                style="animation-duration:1s"></span>
            {{-- icon circle --}}
            <span
                class="relative inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-500/20 border border-green-500/30">
                <svg class="w-7 h-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                </svg>
            </span>
        </div>

        {{-- Info --}}
        <div class="px-6 pb-2">
            <p class="text-fg-muted text-xs font-medium tracking-widest uppercase mb-1">Incoming Call</p>
            <p class="text-fg font-semibold text-lg truncate" x-text="callerNumber || 'Unknown caller'"></p>
            {{-- ringing dots --}}
            <p class="text-fg-muted text-xs mt-1 flex items-center justify-center gap-0.5">
                Ringing
                <span class="inline-flex gap-0.5 ml-1">
                    <span class="w-1 h-1 bg-fg-muted rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1 h-1 bg-fg-muted rounded-full animate-bounce"
                        style="animation-delay:150ms"></span>
                    <span class="w-1 h-1 bg-fg-muted rounded-full animate-bounce"
                        style="animation-delay:300ms"></span>
                </span>
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 px-6 py-5">
            <button @click="$dispatch('reject-call'); show = false"
                class="flex-1 inline-flex items-center justify-center gap-2 py-3 rounded-xl
                           bg-red-600/15 hover:bg-red-600/25 text-red-500 font-semibold text-sm
                           border border-red-500/20 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" />
                </svg>
                Reject
            </button>
            <button @click="$dispatch('accept-call'); show = false"
                class="flex-1 inline-flex items-center justify-center gap-2 py-3 rounded-xl
                           bg-green-600 hover:bg-green-500 text-white font-semibold text-sm
                           shadow-sm transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" />
                </svg>
                Accept
            </button>
        </div>

    </div>
</div>

@push('scripts')
    <script>
        // Twilio Device and Call objects are stored outside Alpine's reactive proxy
        // to avoid "read-only non-configurable property" errors caused by Alpine
        // wrapping Twilio's internal _log and other non-configurable props.
        let _twilioDevice = null;
        let _twilioActiveCall = null;
        let _twilioIncomingCall = null;

        function agentPhone() {
            return {
                token: null,
                identity: null,
                deviceReady: false,
                hasActiveCall: false,
                hasIncomingCall: false,
                callStatus: 'Connecting…',
                callDuration: '',
                _callTimer: null,
                isMuted: false,
                isOnHold: false,
                canAcceptCalls: @json(auth()->user()?->agentStatus?->statusType?->is_available ?? false),

                // ── initialisation ──────────────────────────────────────

                async enableCalling() {
                    if (!this.canAcceptCalls) {
                        window.Toast.show('You are not accepting calls in your current status.', 'warning');
                        return;
                    }
                    if (_twilioDevice) return;
                    try {
                        const res = await fetch('{{ route('twilio.getAccessToken') }}');
                        const data = await res.json();
                        this.token = data.token;
                        this.identity = data.identity;
                        this.initializeDevice();
                    } catch (e) {
                        console.error('Failed to get token', e);
                        window.Toast.show('Failed to initialise calling. Please try again.', 'error');
                    }
                },

                initializeDevice() {
                    _twilioDevice = new window.Device(this.token, {
                        codecPreferences: ['opus', 'pcmu'],
                        logLevel: 1,
                    });

                    _twilioDevice.on('registered', () => {
                        this.deviceReady = true;
                        window.Toast.show('Calling enabled.', 'success');
                    });

                    _twilioDevice.on('error', error => {
                        console.error('Twilio error', error);
                        window.Toast.show('Calling device error. Please reload.', 'error');
                    });

                    _twilioDevice.on('incoming', call => {
                        _twilioIncomingCall = call;
                        this.hasIncomingCall = true;
                        const from = call.parameters.From || '';
                        window.dispatchEvent(new CustomEvent('incoming-call', {
                            detail: from
                        }));

                        const acceptHandler = () => this.acceptIncoming();
                        const rejectHandler = () => this.rejectIncoming();
                        window.addEventListener('accept-call', acceptHandler, {
                            once: true
                        });
                        window.addEventListener('reject-call', rejectHandler, {
                            once: true
                        });
                    });

                    _twilioDevice.register();
                },

                // ── inbound ─────────────────────────────────────────────

                acceptIncoming() {
                    if (!_twilioIncomingCall) return;
                    _twilioIncomingCall.accept();
                    _twilioActiveCall = _twilioIncomingCall;
                    _twilioIncomingCall = null;
                    this.hasIncomingCall = false;
                    this.hasActiveCall = true;
                    this.isMuted = false;
                    this.isOnHold = false;
                    this.callStatus = 'In call…';
                    this.attachCallEvents(_twilioActiveCall);
                },

                rejectIncoming() {
                    if (!_twilioIncomingCall) return;
                    _twilioIncomingCall.reject();
                    _twilioIncomingCall = null;
                    this.hasIncomingCall = false;
                },

                // ── outbound ────────────────────────────────────────────

                openDialer() {
                    if (!this.deviceReady) {
                        window.Toast.show('Enable calling first.', 'warning');
                        return;
                    }
                    window.dispatchEvent(new CustomEvent('open-dialer'));
                },

                makeCall(number) {
                    if (!_twilioDevice || !number) return;
                    this.callStatus = 'Dialling…';
                    this.isMuted = false;
                    this.isOnHold = false;
                    const agent = this.identity;
                    setTimeout(async () => {
                        try {
                            const call = await _twilioDevice.connect({
                                params: {
                                    To: number,
                                    agent: agent,
                                    From: '{{ config('services.twilio.caller_id') }}',
                                }
                            });
                            _twilioActiveCall = call;
                            this.hasActiveCall = true;
                            this.attachCallEvents(call);
                        } catch (e) {
                            console.error('makeCall failed', e);
                            this.callStatus = 'Connecting…';
                            window.Toast.show('Call failed. Please try again.', 'error');
                        }
                    }, 0);
                },

                // ── call events ─────────────────────────────────────────

                attachCallEvents(call) {
                    call.on('accept', () => {
                        this.callStatus = 'In call';
                        this._startTimer();
                    });
                    call.on('disconnect', () => {
                        _twilioActiveCall = null;
                        this.hasActiveCall = false;
                        this.isMuted = false;
                        this.isOnHold = false;
                        this.callStatus = 'Connecting…';
                        this._stopTimer();
                    });
                    call.on('cancel', () => {
                        _twilioActiveCall = null;
                        this.hasActiveCall = false;
                        this.isMuted = false;
                        this.isOnHold = false;
                        this.callStatus = 'Connecting…';
                        this._stopTimer();
                    });
                    call.on('reject', () => {
                        _twilioActiveCall = null;
                        this.hasActiveCall = false;
                        this.isMuted = false;
                        this.isOnHold = false;
                        this.callStatus = 'Connecting…';
                        this._stopTimer();
                    });
                    // Listen for do-transfer dispatched from the transfer modal
                    window.addEventListener('do-transfer', e => this.transferCall(e.detail), {
                        once: true
                    });
                },

                // ── mute ────────────────────────────────────────────────

                toggleMute() {
                    if (!_twilioActiveCall) return;
                    this.isMuted = !this.isMuted;
                    _twilioActiveCall.mute(this.isMuted);
                    this.callStatus = this.isMuted ? 'Muted' : 'In call';
                },

                // ── hold ────────────────────────────────────────────────

                async toggleHold() {
                    if (!_twilioActiveCall) return;
                    const callSid = _twilioActiveCall.parameters?.CallSid;
                    if (!callSid) {
                        window.Toast.show('Call SID not available yet.', 'warning');
                        return;
                    }
                    try {
                        if (!this.isOnHold) {
                            await fetch('{{ route('twilio.holdCall') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ??
                                        ''
                                },
                                body: JSON.stringify({
                                    call_sid: callSid
                                }),
                                credentials: 'same-origin',
                            });
                            this.isOnHold = true;
                            this.callStatus = 'On hold';
                        } else {
                            await fetch('{{ route('twilio.resumeCall') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ??
                                        ''
                                },
                                body: JSON.stringify({
                                    call_sid: callSid
                                }),
                                credentials: 'same-origin',
                            });
                            this.isOnHold = false;
                            this.callStatus = 'In call';
                        }
                    } catch (e) {
                        console.error('Hold/Resume failed', e);
                        window.Toast.show('Could not update hold state. Please try again.', 'error');
                    }
                },

                // ── transfer ────────────────────────────────────────────

                openTransfer() {
                    window.dispatchEvent(new CustomEvent('open-transfer'));
                },

                async transferCall(destination) {
                    if (!_twilioActiveCall || !destination) return;
                    const callSid = _twilioActiveCall.parameters?.CallSid;
                    if (!callSid) {
                        window.Toast.show('Call SID not available — cannot transfer.', 'warning');
                        return;
                    }
                    try {
                        this.callStatus = 'Transferring…';
                        await fetch('{{ route('twilio.transferCall') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? ''
                            },
                            body: JSON.stringify({
                                call_sid: callSid,
                                to: destination
                            }),
                            credentials: 'same-origin',
                        });
                        // Hang up the agent's leg — caller is now ringing the transfer target
                        this.hangUp();
                        window.Toast.show('Call transferred successfully.', 'success');
                    } catch (e) {
                        console.error('Transfer failed', e);
                        this.callStatus = 'In call';
                        window.Toast.show('Transfer failed. Please try again.', 'error');
                    }
                },

                // ── hang up ─────────────────────────────────────────────

                hangUp() {
                    if (_twilioActiveCall) {
                        _twilioActiveCall.disconnect();
                        _twilioActiveCall = null;
                        this.hasActiveCall = false;
                        this.isMuted = false;
                        this.isOnHold = false;
                        this._stopTimer();
                    }
                },

                // ── timer helpers ───────────────────────────────────────

                _startTimer() {
                    this._stopTimer();
                    this.callDuration = '00:00';
                    let secs = 0;
                    this._callTimer = setInterval(() => {
                        secs++;
                        const m = String(Math.floor(secs / 60)).padStart(2, '0');
                        const s = String(secs % 60).padStart(2, '0');
                        this.callDuration = `${m}:${s}`;
                    }, 1000);
                },

                _stopTimer() {
                    if (this._callTimer) {
                        clearInterval(this._callTimer);
                        this._callTimer = null;
                    }
                    this.callDuration = '';
                },
            }
        }
    </script>
@endpush
