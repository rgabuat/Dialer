{{-- ══════════════════════════════════════════════════════════════════
     Agent Control Bar — topbar widget
     Tokens used: bg-surface, border-surface, text-fg, text-fg-muted
     Dark / light mode handled automatically via CSS custom properties.
     ══════════════════════════════════════════════════════════════════ --}}
@php
    $authStatusType = auth()->user()?->agentStatus?->statusType;
@endphp
@persist('agent-phone-bar')
    <div x-data="agentPhone()" @make-call.window="makeCall($event.detail)"
        @hangup-call.window="if (window._twilioActiveCall) { window._twilioActiveCall.disconnect(); }"
        @agent-status-changed.window="
        const d = $event.detail || ($event.detail?.[0] ?? {});
        const handlesInbound  = d.handles_inbound  ?? false;
        const handlesOutbound = d.handles_outbound ?? false;
        canAcceptCalls  = handlesInbound;
        canMakeOutbound = handlesOutbound;
        if (handlesInbound || handlesOutbound) {
            if (!deviceReady && !initializing) enableCalling();
        } else {
            disableCalling();
        }
    "
        class="flex items-center gap-2">

        {{-- ── CALL CONTROLS pill ──────────────────────────────────── --}}
        <div x-show="initializing || deviceReady || hasActiveCall || hasIncomingCall"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" x-cloak
            class="flex items-center gap-1 bg-surface px-1.5 py-1 border border-surface rounded-lg">

            {{-- INCOMING CALL — inline topbar (replaces modal) --}}
            <div x-show="hasIncomingCall" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 -translate-x-1"
                x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-x-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-x-1" class="flex items-center gap-2">

                {{-- Pulsing ring --}}
                <span class="inline-flex relative shrink-0 w-2 h-2">
                    <span
                        class="inline-flex absolute bg-green-400 opacity-75 rounded-full w-full h-full animate-ping"></span>
                    <span class="inline-flex relative bg-green-400 rounded-full w-2 h-2"></span>
                </span>

                {{-- Caller info --}}
                <div class="leading-tight">
                    <p class="font-semibold text-fg text-xs leading-none" x-text="incomingCallerNumber || 'Unknown caller'">
                    </p>
                    <p class="mt-0.5 text-[10px] text-fg-muted leading-none">Incoming Call</p>
                </div>

                {{-- Reject --}}
                <button @click="$dispatch('reject-call')" title="Reject"
                    class="inline-flex justify-center items-center bg-red-600/15 hover:bg-red-600/30 border border-red-500/30 rounded-full w-7 h-7 text-red-500 transition-all duration-150 shrink-0">
                    <svg class="w-3.5 h-3.5 rotate-[135deg]" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" />
                    </svg>
                </button>

                {{-- Accept --}}
                <button @click="$dispatch('accept-call')" title="Accept"
                    class="inline-flex justify-center items-center bg-green-600 hover:bg-green-500 shadow-sm rounded-full w-7 h-7 text-white transition-all duration-150 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" />
                    </svg>
                </button>

            </div>

            {{-- Normal controls (hidden while an incoming call is ringing) --}}
            <div x-show="!hasIncomingCall">

                {{-- INITIALIZING — device is registering with Twilio --}}
                <template x-if="initializing && !deviceReady && !hasActiveCall">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 font-medium text-fg-muted text-xs">
                            <span class="inline-flex relative w-2 h-2">
                                <span
                                    class="inline-flex absolute bg-yellow-400 opacity-75 rounded-full w-full h-full animate-ping"></span>
                                <span class="inline-flex relative bg-yellow-400 rounded-full w-2 h-2"></span>
                            </span>
                            Initializing
                        </span>
                    </div>
                </template>

                {{-- READY — no active call: green dot + Dial button (outbound only) --}}
                <template x-if="deviceReady && !hasActiveCall">
                    <div class="flex items-center gap-2">
                        {{-- Ready indicator --}}
                        <span class="inline-flex items-center gap-1.5 font-medium text-fg-muted text-xs">
                            <span class="inline-block bg-green-500 rounded-full w-2 h-2"></span>
                            Ready
                        </span>
                        {{-- Dial button — only outbound status can initiate calls --}}
                        <template x-if="canMakeOutbound">
                            <button @click="openDialer"
                                class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-hover px-3 py-1.5 rounded-md font-semibold text-fg text-xs transition-all duration-150">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                                </svg>
                                Dial
                            </button>
                        </template>
                    </div>
                </template>

                {{-- ACTIVE CALL: pulsing dot + timer + status + action buttons --}}
                <template x-if="hasActiveCall">
                    <div class="flex items-center gap-2">
                        {{-- Live pulse indicator --}}
                        <span class="inline-flex relative w-2.5 h-2.5">
                            <span
                                class="inline-flex absolute bg-red-400 opacity-75 rounded-full w-full h-full animate-ping"></span>
                            <span class="inline-flex relative bg-red-500 rounded-full w-2.5 h-2.5"></span>
                        </span>
                        {{-- Status + elapsed timer --}}
                        <span class="font-mono font-medium text-fg text-xs tracking-wide"
                            x-text="callStatus + (callDuration ? '  ' + callDuration : '')"></span>

                        {{-- Mute toggle --}}
                        <button @click="toggleMute" :title="isMuted ? 'Unmute' : 'Mute'"
                            :class="isMuted
                                ?
                                'bg-yellow-500/20 text-yellow-400 border-yellow-500/30' :
                                'bg-surface-2 text-fg-muted border-surface hover:bg-hover'"
                            class="inline-flex justify-center items-center border rounded-md w-7 h-7 text-xs transition-all duration-150">
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
                            class="inline-flex justify-center items-center border rounded-md w-7 h-7 text-xs transition-all duration-150">
                            <template x-if="!isOnHold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
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
                            class="inline-flex justify-center items-center bg-surface-2 hover:bg-hover border border-surface rounded-md w-7 h-7 text-fg-muted text-xs transition-all duration-150">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </button>

                        {{-- Hang Up --}}
                        <button @click="hangUp"
                            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-500 shadow-sm px-3 py-1.5 rounded-md font-semibold text-white text-xs transition-all duration-150">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 3.75a.75.75 0 0 1 .75.75V8.25a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75V4.5a.75.75 0 0 1 .75-.75h4.5ZM9 15.75a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-.75.75H4.5a.75.75 0 0 1-.75-.75V16.5a.75.75 0 0 1 .75-.75H9ZM3.375 7.5A.375.375 0 0 0 3 7.875v8.25c0 .207.168.375.375.375h17.25A.375.375 0 0 0 21 16.125V7.875A.375.375 0 0 0 20.625 7.5H3.375Z" />
                            </svg>
                            Hang Up
                        </button>
                    </div>
                </template>

            </div>{{-- end normal controls --}}

        </div>

        {{-- ── SEPARATOR ───────────────────────────────────────────── --}}
        <div x-show="initializing || deviceReady || hasActiveCall || hasIncomingCall" x-cloak
            class="bg-surface-2 w-px h-5"></div>

        {{-- ── AGENT STATUS switcher ───────────────────────────────── --}}
        <div class="flex items-center bg-surface px-1.5 py-1 border border-surface rounded-lg">
            @livewire('agent.status-switcher')
        </div>

    </div>
@endpersist


{{-- ══════════════════════════════════════════════════════════════════
     DIAL MODAL — numeric keypad dialer
     ══════════════════════════════════════════════════════════════════ --}}
@persist('dial-modal')
    {{-- ── DIAL POPOVER — opens below the Dial button, never blocks navigation ── --}}
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
        x-on:open-dialer.window="open = true; digits = ''; $nextTick(() => $refs.dialDisplay?.focus())"
        @keydown.escape.window="open = false">

        {{-- Popover panel — fixed below topbar, right-aligned, no overlay --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-1" @click.outside="open = false"
            class="top-[4.25rem] right-4 z-50 fixed bg-surface shadow-2xl border border-surface rounded-2xl w-72 overflow-hidden"
            style="transform-origin: top right;">

            {{-- Header --}}
            <div class="flex justify-between items-center px-4 py-3 border-surface border-b">
                <div class="flex items-center gap-2">
                    <span class="inline-flex justify-center items-center bg-green-500/15 rounded-full w-6 h-6">
                        <svg class="w-3 h-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                        </svg>
                    </span>
                    <span class="font-semibold text-fg text-sm">Outbound Call</span>
                </div>
                <button @click="open = false"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-6 h-6 text-fg-muted hover:text-fg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Number display --}}
            <div class="px-4 pt-3 pb-2">
                <div class="relative flex items-center">
                    <input x-ref="dialDisplay" x-model="digits" type="tel" placeholder="+1 (000) 000-0000"
                        @keydown.enter="call()"
                        class="bg-surface-2 px-4 py-2.5 pr-9 border border-surface rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 w-full font-mono text-fg text-sm text-center tracking-widest transition placeholder-fg-muted" />
                    {{-- Backspace --}}
                    <button @click="del()" x-show="digits.length > 0"
                        class="right-2.5 absolute text-fg-muted hover:text-fg transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9.75 14.25 12m0 0 2.25 2.25M14.25 12l2.25-2.25M14.25 12 12 14.25m-2.58 4.92-6.374-6.375a1.125 1.125 0 0 1 0-1.59L9.42 4.83c.21-.211.497-.33.795-.33H19.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25h-9.284c-.298 0-.585-.119-.795-.33Z" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Keypad --}}
            <div class="gap-1.5 grid grid-cols-3 px-4 pt-2 pb-3">
                @foreach ([['1', ''], ['2', 'ABC'], ['3', 'DEF'], ['4', 'GHI'], ['5', 'JKL'], ['6', 'MNO'], ['7', 'PQRS'], ['8', 'TUV'], ['9', 'WXYZ'], ['*', ''], ['0', '+'], ['#', '']] as [$digit, $sub])
                    <button @click="press('{{ $digit }}')"
                        class="flex flex-col justify-center items-center bg-surface-2 hover:bg-hover border border-surface rounded-xl h-12 font-semibold text-fg text-sm active:scale-95 transition-all duration-100 select-none">
                        {{ $digit }}
                        @if ($sub)
                            <span
                                class="mt-0.5 font-normal text-[8px] text-fg-muted tracking-widest">{{ $sub }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Call button --}}
            <div class="px-4 pb-4">
                <button @click="call()" :disabled="!digits"
                    :class="digits ? 'bg-green-600 hover:bg-green-500 text-white shadow-sm' :
                        'bg-surface-2 text-fg-muted opacity-50 cursor-not-allowed'"
                    class="inline-flex justify-center items-center gap-2 py-2.5 rounded-xl w-full font-semibold text-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                    </svg>
                    Call
                </button>
            </div>

        </div>
    </div>
@endpersist


{{-- ══════════════════════════════════════════════════════════════════
     TRANSFER MODAL — blind (cold) transfer to number or agent
     ══════════════════════════════════════════════════════════════════ --}}
@persist('transfer-modal')
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
        class="z-50 fixed inset-0 flex justify-center items-center bg-black/70 px-4">

        <div class="absolute inset-0" @click="open = false"></div>

        <div class="z-10 relative bg-surface shadow-2xl border border-surface rounded-2xl w-full max-w-sm overflow-hidden animate-modal-in"
            @click.stop>

            {{-- Header --}}
            <div class="flex justify-between items-center px-5 py-4 border-surface border-b">
                <div class="flex items-center gap-2">
                    <span class="inline-flex justify-center items-center bg-indigo-500/15 rounded-full w-7 h-7">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </span>
                    <h2 class="font-semibold text-fg text-sm">Transfer Call</h2>
                </div>
                <button @click="open = false"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex border-surface border-b text-sm">
                <button @click="tab='number'"
                    :class="tab === 'number' ? 'border-b-2 border-indigo-500 text-fg font-semibold' :
                        'text-fg-muted hover:text-fg'"
                    class="flex-1 py-3 transition">External Number</button>
                <button @click="tab='agent'"
                    :class="tab === 'agent' ? 'border-b-2 border-indigo-500 text-fg font-semibold' :
                        'text-fg-muted hover:text-fg'"
                    class="flex-1 py-3 transition">Agent</button>
            </div>

            {{-- Number tab --}}
            <div x-show="tab==='number'" class="space-y-3 px-5 py-4">
                <p class="text-fg-muted text-xs">Enter the number to transfer this call to.</p>
                <input x-model="destination" type="tel" placeholder="+1 (000) 000-0000"
                    @keydown.enter="$dispatch('do-transfer', destination); open = false"
                    class="bg-surface-2 px-4 py-3 border border-surface rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 w-full font-mono text-fg text-sm text-center tracking-widest transition placeholder-fg-muted" />
            </div>

            {{-- Agent tab --}}
            <div x-show="tab==='agent'" class="px-5 py-4">
                <p class="mb-3 text-fg-muted text-xs">Select an online agent to transfer this call to.</p>
                <div class="space-y-1 max-h-52 overflow-y-auto">
                    <template x-if="agents.length === 0">
                        <p class="py-3 text-fg-muted text-xs text-center">No agents currently on Phones status.</p>
                    </template>
                    <template x-for="agent in agents" :key="agent.id">
                        <button
                            @click="destination = agent.identity; $dispatch('do-transfer', agent.identity); open = false"
                            class="flex items-center gap-3 bg-surface-2 hover:bg-hover px-3 py-2.5 rounded-lg w-full text-fg text-sm transition">
                            <span
                                class="inline-flex justify-center items-center bg-indigo-500/20 rounded-full w-7 h-7 font-bold text-indigo-400 text-xs shrink-0"
                                x-text="agent.name.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase()"></span>
                            <span x-text="agent.name" class="truncate"></span>
                            <span class="bg-green-500 ml-auto rounded-full w-2 h-2 shrink-0"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 px-5 pb-5">
                <button @click="open = false"
                    class="flex-1 bg-surface-2 hover:bg-hover py-2.5 rounded-xl font-medium text-fg-muted text-sm transition">
                    Cancel
                </button>
                <button x-show="tab==='number'"
                    @click="if(destination){ $dispatch('do-transfer', destination); open = false; }"
                    :disabled="!destination"
                    :class="destination ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm' :
                        'bg-surface-2 text-fg-muted opacity-50 cursor-not-allowed'"
                    class="inline-flex flex-1 justify-center items-center gap-2 py-2.5 rounded-xl font-semibold text-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    Transfer
                </button>
            </div>
        </div>
    </div>
@endpersist



@push('scripts')
    <script>
        // ── Persistent Twilio globals ────────────────────────────────────────────────
        // Stored on window so they survive wire:navigate page transitions.
        // Guard-checks ensure these are never reset when the script is re-evaluated.
        if (!('_twilioDevice' in window)) window._twilioDevice = null;
        if (!('_twilioActiveCall' in window)) window._twilioActiveCall = null;
        if (!('_twilioIncomingCall' in window)) window._twilioIncomingCall = null;
        if (!('_twilioCallStartedAt' in window)) window._twilioCallStartedAt = null;
        if (!('_twilioIdentity' in window)) window._twilioIdentity = null;

        function agentPhone() {
            return {
                token: null,
                identity: null,
                deviceReady: false,
                initializing: false,
                hasActiveCall: false,
                hasIncomingCall: false,
                incomingCallerNumber: '',
                callStatus: 'Connecting…',
                callDuration: '',
                _callTimer: null,
                isMuted: false,
                isOnHold: false,
                canAcceptCalls: @json((bool) ($authStatusType?->handles_inbound ?? false)),
                canMakeOutbound: @json((bool) ($authStatusType?->handles_outbound ?? false)),

                // ── restore state after wire:navigate ────────────────────────────────
                // Alpine may re-initialise this component when Livewire morphs the DOM.
                // init() pulls live state back from the window-level globals so the call
                // UI is restored immediately on the new page.

                init() {
                    // Restore identity so outbound calls work after navigation
                    if (window._twilioIdentity) this.identity = window._twilioIdentity;

                    if (window._twilioDevice) {
                        this.deviceReady = (window._twilioDevice.state === 'registered');
                        // Re-bind device events to this (new) Alpine instance
                        this._rebindDeviceEvents();
                    }
                    if (window._twilioActiveCall) {
                        this.hasActiveCall = true;
                        this.callStatus = 'In call';
                        this.isMuted = window._twilioActiveCall.isMuted?.() ?? false;
                        // Re-start the elapsed timer anchored to the original call start
                        if (window._twilioCallStartedAt) this._startTimer();
                        // Ensure the call-ended handler points to this Alpine instance
                        window._twilioActiveCall.on('disconnect', () => this._onCallEnded());
                        window._twilioActiveCall.on('cancel', () => this._onCallEnded());
                    }
                    if (window._twilioIncomingCall) {
                        this.hasIncomingCall = true;
                        this.incomingCallerNumber = window._twilioIncomingCall.parameters?.From || '';
                        // Caller hung up (or <Dial> timed out) before agent answered
                        const onPreAcceptEnd = () => {
                            if (this.hasIncomingCall) {
                                window._twilioIncomingCall = null;
                                this.hasIncomingCall = false;
                                this.incomingCallerNumber = '';
                            }
                        };
                        window._twilioIncomingCall.on('cancel', onPreAcceptEnd);
                        window._twilioIncomingCall.on('disconnect', onPreAcceptEnd);
                        window.addEventListener('accept-call', () => this.acceptIncoming(), {
                            once: true
                        });
                        window.addEventListener('reject-call', () => this.rejectIncoming(), {
                            once: true
                        });
                    }

                    // Auto-start device on fresh page load if status is already call-eligible
                    if (!window._twilioDevice) {
                        const handlesInbound = @json((bool) ($authStatusType->handles_inbound ?? false));
                        const handlesOutbound = @json((bool) ($authStatusType->handles_outbound ?? false));
                        if (handlesInbound || handlesOutbound) {
                            this.$nextTick(() => this.enableCalling());
                        }
                    }
                },

                // ── initialisation ──────────────────────────────────────

                async enableCalling() {
                    if (window._twilioDevice) return;
                    this.initializing = true;
                    try {
                        const res = await fetch('{{ route('twilio.getAccessToken') }}');
                        const data = await res.json();
                        this.token = data.token;
                        this.identity = data.identity;
                        window._twilioIdentity = data.identity;
                        this.initializeDevice();
                    } catch (e) {
                        this.initializing = false;
                        console.error('Failed to get token', e);
                        window.Toast.show('Failed to initialise calling. Please try again.', 'error');
                    }
                },

                initializeDevice() {
                    window._twilioDevice = new window.Device(this.token, {
                        codecPreferences: ['opus', 'pcmu'],
                        logLevel: 1,
                    });
                    this._rebindDeviceEvents();
                    window._twilioDevice.register();
                },

                disableCalling() {
                    if (window._twilioActiveCall) {
                        window._twilioActiveCall.disconnect();
                        window._twilioActiveCall = null;
                        window._twilioCallStartedAt = null;
                    }
                    if (window._twilioDevice) {
                        try {
                            window._twilioDevice.destroy();
                        } catch (_) {}
                        window._twilioDevice = null;
                    }
                    this.deviceReady = false;
                    this.initializing = false;
                    this.hasActiveCall = false;
                    this.hasIncomingCall = false;
                    this._stopTimer();
                },

                // Bind (or re-bind) device-level events to the current Alpine instance.
                // Removes previous listeners first so navigating between pages never
                // accumulates duplicate handlers.
                _rebindDeviceEvents() {
                    window._twilioDevice.removeAllListeners('registered');
                    window._twilioDevice.removeAllListeners('error');
                    window._twilioDevice.removeAllListeners('incoming');

                    window._twilioDevice.on('registered', () => {
                        this.initializing = false;
                        this.deviceReady = true;
                    });

                    window._twilioDevice.on('error', error => {
                        this.initializing = false;
                        console.error('Twilio error', error);
                        window.Toast.show('Calling device error. Please reload.', 'error');
                    });

                    window._twilioDevice.on('incoming', call => {
                        window._twilioIncomingCall = call;
                        this.hasIncomingCall = true;
                        this.incomingCallerNumber = call.parameters.From || '';
                        // Caller hung up (or <Dial> timed out) before agent answered
                        const onPreAcceptEnd = () => {
                            if (this.hasIncomingCall) {
                                window._twilioIncomingCall = null;
                                this.hasIncomingCall = false;
                                this.incomingCallerNumber = '';
                            }
                        };
                        call.on('cancel', onPreAcceptEnd);
                        call.on('disconnect', onPreAcceptEnd);
                        window.addEventListener('accept-call', () => this.acceptIncoming(), {
                            once: true
                        });
                        window.addEventListener('reject-call', () => this.rejectIncoming(), {
                            once: true
                        });
                    });
                },

                // ── inbound ─────────────────────────────────────────────

                acceptIncoming() {
                    if (!window._twilioIncomingCall) return;
                    window._twilioIncomingCall.accept();
                    window._twilioActiveCall = window._twilioIncomingCall;
                    window._twilioIncomingCall = null;
                    this.hasIncomingCall = false;
                    this.hasActiveCall = true;
                    this.isMuted = false;
                    this.isOnHold = false;
                    this.callStatus = 'In call…';
                    this.attachCallEvents(window._twilioActiveCall);
                },

                rejectIncoming() {
                    if (!window._twilioIncomingCall) return;
                    window._twilioIncomingCall.reject();
                    window._twilioIncomingCall = null;
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

                makeCall(numberOrObj) {
                    const number = (typeof numberOrObj === 'object' && numberOrObj !== null) ?
                        (numberOrObj.phone ?? numberOrObj.To ?? '') :
                        numberOrObj;
                    if (!window._twilioDevice || !number) return;
                    this.callStatus = 'Dialling…';
                    this.isMuted = false;
                    this.isOnHold = false;
                    const agent = this.identity;
                    setTimeout(async () => {
                        try {
                            const call = await window._twilioDevice.connect({
                                params: {
                                    To: number,
                                    agent: agent,
                                    From: '{{ config('services.twilio.caller_id') }}',
                                }
                            });
                            window._twilioActiveCall = call;
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
                    call.on('disconnect', () => this._onCallEnded());
                    call.on('cancel', () => this._onCallEnded());
                    call.on('reject', () => this._onCallEnded());
                    // Listen for do-transfer dispatched from the transfer modal
                    window.addEventListener('do-transfer', e => this.transferCall(e.detail), {
                        once: true
                    });
                },

                // Central teardown called by disconnect / cancel / reject events.
                _onCallEnded() {
                    window._twilioActiveCall = null;
                    window._twilioCallStartedAt = null; // clear start time only on actual call end
                    this.hasActiveCall = false;
                    this.isMuted = false;
                    this.isOnHold = false;
                    this.callStatus = 'Connecting…';
                    this._stopTimer();
                    // Notify DialerPanel that call has ended
                    window.dispatchEvent(new CustomEvent('call-ended'));
                },

                // ── mute ────────────────────────────────────────────────

                toggleMute() {
                    if (!window._twilioActiveCall) return;
                    this.isMuted = !this.isMuted;
                    window._twilioActiveCall.mute(this.isMuted);
                    this.callStatus = this.isMuted ? 'Muted' : 'In call';
                },

                // ── hold ────────────────────────────────────────────────

                async toggleHold() {
                    if (!window._twilioActiveCall) return;
                    const callSid = window._twilioActiveCall.parameters?.CallSid;
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
                    if (!window._twilioActiveCall || !destination) return;
                    const callSid = window._twilioActiveCall.parameters?.CallSid;
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
                    if (window._twilioActiveCall) {
                        window._twilioActiveCall.disconnect();
                        window._twilioActiveCall = null;
                        window._twilioCallStartedAt = null;
                        this.hasActiveCall = false;
                        this.isMuted = false;
                        this.isOnHold = false;
                        this._stopTimer();
                    }
                },

                // ── timer helpers ───────────────────────────────────────
                // The timer is anchored to window._twilioCallStartedAt (a wall-clock
                // timestamp) so the elapsed time is accurate even after navigation.

                _startTimer() {
                    this._stopTimer();
                    if (!window._twilioCallStartedAt) window._twilioCallStartedAt = Date.now();
                    const tick = () => {
                        const secs = Math.floor((Date.now() - window._twilioCallStartedAt) / 1000);
                        const m = String(Math.floor(secs / 60)).padStart(2, '0');
                        const s = String(secs % 60).padStart(2, '0');
                        this.callDuration = `${m}:${s}`;
                    };
                    tick();
                    this._callTimer = setInterval(tick, 1000);
                },

                _stopTimer() {
                    if (this._callTimer) {
                        clearInterval(this._callTimer);
                        this._callTimer = null;
                    }
                    // Do NOT reset window._twilioCallStartedAt here — it must survive
                    // wire:navigate page transitions so the timer continues from where
                    // it left off. Only _onCallEnded() and hangUp() clear it.
                    this.callDuration = '';
                },
            }
        }
    </script>
@endpush
