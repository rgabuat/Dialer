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
        const d = Array.isArray($event.detail) ? ($event.detail[0] ?? {}) : ($event.detail ?? {});
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

                        {{-- Whisper / consult --}}
                        <button @click="openWhisper" title="Whisper to another agent" :disabled="hasConsultCall"
                            :class="hasConsultCall
                                ?
                                'bg-sky-500/20 text-sky-300 border-sky-500/30' :
                                'bg-surface-2 hover:bg-hover text-fg-muted border-surface'"
                            class="inline-flex justify-center items-center border rounded-md w-7 h-7 text-xs transition-all duration-150 disabled:opacity-100">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12.76c0 1.6 1.123 2.994 2.693 3.343l2.291.509a1.125 1.125 0 0 1 .814.652l1.01 2.246a1.125 1.125 0 0 0 1.936.204l1.286-1.715a1.125 1.125 0 0 1 1.11-.42l2.5.5a3.375 3.375 0 0 0 3.981-3.31V7.5a3.375 3.375 0 0 0-3.981-3.31l-2.5.5a1.125 1.125 0 0 1-1.11-.42L10.994 2.555a1.125 1.125 0 0 0-1.936.204l-1.01 2.246a1.125 1.125 0 0 1-.814.652l-2.291.509A3.375 3.375 0 0 0 2.25 9.74v3.02Z" />
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
        mode: 'transfer',
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
    }"
        x-on:open-transfer.window="open = true; mode = 'transfer'; destination = ''; tab = 'number'; loadAgents()"
        x-on:open-whisper.window="open = true; mode = 'whisper'; destination = ''; tab = 'agent'; loadAgents()"
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
                            <path x-show="mode === 'transfer'" stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            <path x-show="mode === 'whisper'" stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12.76c0 1.6 1.123 2.994 2.693 3.343l2.291.509a1.125 1.125 0 0 1 .814.652l1.01 2.246a1.125 1.125 0 0 0 1.936.204l1.286-1.715a1.125 1.125 0 0 1 1.11-.42l2.5.5a3.375 3.375 0 0 0 3.981-3.31V7.5a3.375 3.375 0 0 0-3.981-3.31l-2.5.5a1.125 1.125 0 0 1-1.11-.42L10.994 2.555a1.125 1.125 0 0 0-1.936.204l-1.01 2.246a1.125 1.125 0 0 1-.814.652l-2.291.509A3.375 3.375 0 0 0 2.25 9.74v3.02Z" />
                        </svg>
                    </span>
                    <h2 class="font-semibold text-fg text-sm"
                        x-text="mode === 'whisper' ? 'Whisper Consult' : 'Transfer Call'"></h2>
                </div>
                <button @click="open = false"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div x-show="mode === 'transfer'" class="flex border-surface border-b text-sm">
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
                <p class="mb-3 text-fg-muted text-xs"
                    x-text="mode === 'whisper' ? 'Select an agent for a private consult while the client stays on hold.' : 'Select an online agent to transfer this call to.'">
                </p>
                <div class="space-y-1 max-h-52 overflow-y-auto">
                    <template x-if="agents.length === 0">
                        <p class="py-3 text-fg-muted text-xs text-center">No agents currently on Phones status.</p>
                    </template>
                    <template x-for="agent in agents" :key="agent.id">
                        <button
                            @click="destination = agent.identity; $dispatch(mode === 'whisper' ? 'do-whisper' : 'do-transfer', agent.identity); open = false"
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
                <button x-show="mode === 'transfer' && tab==='number'"
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
                <button x-show="mode === 'whisper'"
                    @click="if(destination){ $dispatch('do-whisper', destination); open = false; }"
                    :disabled="!destination"
                    :class="destination ? 'bg-sky-600 hover:bg-sky-500 text-white shadow-sm' :
                        'bg-surface-2 text-fg-muted opacity-50 cursor-not-allowed'"
                    class="inline-flex flex-1 justify-center items-center gap-2 py-2.5 rounded-xl font-semibold text-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12.76c0 1.6 1.123 2.994 2.693 3.343l2.291.509a1.125 1.125 0 0 1 .814.652l1.01 2.246a1.125 1.125 0 0 0 1.936.204l1.286-1.715a1.125 1.125 0 0 1 1.11-.42l2.5.5a3.375 3.375 0 0 0 3.981-3.31V7.5a3.375 3.375 0 0 0-3.981-3.31l-2.5.5a1.125 1.125 0 0 1-1.11-.42L10.994 2.555a1.125 1.125 0 0 0-1.936.204l-1.01 2.246a1.125 1.125 0 0 1-.814.652l-2.291.509A3.375 3.375 0 0 0 2.25 9.74v3.02Z" />
                    </svg>
                    Start Whisper
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
        if (!('_twilioConsultCall' in window)) window._twilioConsultCall = null;
        if (!('_twilioIncomingCall' in window)) window._twilioIncomingCall = null;
        if (!('_twilioCallStartedAt' in window)) window._twilioCallStartedAt = null;
        if (!('_twilioIdentity' in window)) window._twilioIdentity = null;
        if (!('_twilioHeldCallSid' in window)) window._twilioHeldCallSid = null;
        if (!('_twilioAutoAcceptNextIncoming' in window)) window._twilioAutoAcceptNextIncoming = false;

        // Digits-only list of numbers that belong to this platform.
        // Agents are blocked from dialling these to prevent IVR loops.
        const _platformNumbers = [];

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
                hasConsultCall: false,
                _holdingForConsult: false,
                canAcceptCalls: @json((bool) ($authStatusType?->handles_inbound ?? false)),
                canMakeOutbound: @json((bool) ($authStatusType?->handles_outbound ?? false)),

                // ── restore state after wire:navigate ────────────────────────────────
                // Alpine may re-initialise this component when Livewire morphs the DOM.
                // init() pulls live state back from the window-level globals so the call
                // UI is restored immediately on the new page.

                init() {
                    console.log('[AgentPhone] init()', {
                        identity: window._twilioIdentity,
                        heldCallSid: window._twilioHeldCallSid,
                        deviceState: window._twilioDevice?.state ?? null,
                        hasActiveCall: !!window._twilioActiveCall,
                        hasConsultCall: !!window._twilioConsultCall,
                        hasIncomingCall: !!window._twilioIncomingCall,
                        callStartedAt: window._twilioCallStartedAt,
                        canAcceptCalls: this.canAcceptCalls,
                        canMakeOutbound: this.canMakeOutbound,
                    });

                    // Restore identity so outbound calls work after navigation
                    if (window._twilioIdentity) this.identity = window._twilioIdentity;
                    if (window._twilioHeldCallSid) {
                        this.isOnHold = true;
                        this.callStatus = 'Customer on hold';
                        console.log('[AgentPhone] init: restoring held call', window._twilioHeldCallSid);
                    }

                    if (window._twilioDevice) {
                        this.deviceReady = (window._twilioDevice.state === 'registered');
                        console.log('[AgentPhone] init: existing device found, state =', window._twilioDevice.state);
                        // Re-bind device events to this (new) Alpine instance
                        this._rebindDeviceEvents();
                    }
                    if (window._twilioActiveCall) {
                        this.hasActiveCall = true;
                        this.callStatus = 'In call';
                        this.isMuted = window._twilioActiveCall.isMuted?.() ?? false;
                        console.log('[AgentPhone] init: restoring active call', {
                            callSid: window._twilioActiveCall.parameters?.CallSid,
                            isMuted: this.isMuted,
                            callStartedAt: window._twilioCallStartedAt,
                        });
                        // Re-start the elapsed timer anchored to the original call start
                        if (window._twilioCallStartedAt) this._startTimer();
                        // Ensure the call-ended handler points to this Alpine instance
                        window._twilioActiveCall.on('disconnect', () => this._onCallEnded());
                        window._twilioActiveCall.on('cancel', () => this._onCallEnded());
                    }
                    if (window._twilioConsultCall) {
                        this.hasConsultCall = true;
                        this.callStatus = 'Private consult';
                        console.log('[AgentPhone] init: restoring consult call', {
                            callSid: window._twilioConsultCall.parameters?.CallSid,
                        });
                        window._twilioConsultCall.on('disconnect', () => this._onConsultEnded());
                        window._twilioConsultCall.on('cancel', () => this._onConsultEnded());
                        window._twilioConsultCall.on('reject', () => this._onConsultEnded());
                    }
                    if (window._twilioIncomingCall) {
                        this.hasIncomingCall = true;
                        this.incomingCallerNumber = window._twilioIncomingCall.parameters?.From || '';
                        console.log('[AgentPhone] init: restoring incoming call', {
                            callSid: window._twilioIncomingCall.parameters?.CallSid,
                            from: this.incomingCallerNumber,
                        });
                        // Caller hung up (or <Dial> timed out) before agent answered
                        const onPreAcceptEnd = () => {
                            if (this.hasIncomingCall) {
                                console.log('[AgentPhone] incoming call ended before answer (cancel/disconnect)');
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
                        console.log('[AgentPhone] init: no device yet, checking status flags', {
                            handlesInbound,
                            handlesOutbound
                        });
                        if (handlesInbound || handlesOutbound) {
                            this.$nextTick(() => this.enableCalling());
                        }
                    }

                    // Listen for supervisor monitoring prep — sets auto-accept so the
                    // agent seamlessly rejoins when moved into a conference.
                    if (window.Echo) {
                        window.Echo.private('App.Models.User.{{ auth()->id() }}')
                            .listen('.MonitorPrepare', (e) => {
                                console.log('[AgentPhone] MonitorPrepare — enabling auto-accept for conference', e);
                                window._twilioAutoAcceptNextIncoming = true;
                            });
                    }
                },

                // ── initialisation ──────────────────────────────────────

                async enableCalling() {
                    if (window._twilioDevice) {
                        console.log('[AgentPhone] enableCalling: device already exists, skipping');
                        return;
                    }
                    console.log('[AgentPhone] enableCalling: fetching access token…');
                    this.initializing = true;
                    try {
                        const res = await fetch('{{ route('twilio.getAccessToken') }}');
                        const data = await res.json();
                        console.log('[AgentPhone] enableCalling: token received', {
                            identity: data.identity
                        });
                        this.token = data.token;
                        this.identity = data.identity;
                        window._twilioIdentity = data.identity;
                        this.initializeDevice();
                    } catch (e) {
                        this.initializing = false;
                        console.error('[AgentPhone] enableCalling: failed to get token', e);
                        window.Toast.show('Failed to initialise calling. Please try again.', 'error');
                    }
                },

                initializeDevice() {
                    console.log('[AgentPhone] initializeDevice: creating Twilio Device', {
                        identity: this.identity
                    });
                    window._twilioDevice = new window.Device(this.token, {
                        codecPreferences: ['opus', 'pcmu'],
                        logLevel: 1,
                    });
                    this._rebindDeviceEvents();
                    window._twilioDevice.register();
                    console.log('[AgentPhone] initializeDevice: register() called');
                },

                disableCalling() {
                    console.log('[AgentPhone] disableCalling: tearing down device and calls');
                    if (window._twilioActiveCall) {
                        window._twilioActiveCall.disconnect();
                        window._twilioActiveCall = null;
                        window._twilioCallStartedAt = null;
                    }
                    if (window._twilioConsultCall) {
                        window._twilioConsultCall.disconnect();
                        window._twilioConsultCall = null;
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
                    this.hasConsultCall = false;
                    this.isOnHold = false;
                    this._holdingForConsult = false;
                    window._twilioHeldCallSid = null;
                    window._twilioAutoAcceptNextIncoming = false;
                    this._stopTimer();
                    console.log('[AgentPhone] disableCalling: done');
                },

                // Bind (or re-bind) device-level events to the current Alpine instance.
                // Removes previous listeners first so navigating between pages never
                // accumulates duplicate handlers.
                _rebindDeviceEvents() {
                    console.log('[AgentPhone] _rebindDeviceEvents: rebinding registered/error/incoming listeners');
                    window._twilioDevice.removeAllListeners('registered');
                    window._twilioDevice.removeAllListeners('error');
                    window._twilioDevice.removeAllListeners('incoming');

                    window._twilioDevice.on('registered', () => {
                        console.log('[AgentPhone] device: registered ✓');
                        this.initializing = false;
                        this.deviceReady = true;
                    });

                    window._twilioDevice.on('error', error => {
                        console.error('[AgentPhone] device: error', error);
                        this.initializing = false;
                        window.Toast.show('Calling device error. Please reload.', 'error');
                    });

                    window._twilioDevice.on('incoming', call => {
                        console.log('[AgentPhone] device: incoming call', {
                            callSid: call.parameters?.CallSid,
                            from: call.parameters?.From,
                            autoAccept: window._twilioAutoAcceptNextIncoming,
                        });
                        if (window._twilioAutoAcceptNextIncoming) {
                            console.log('[AgentPhone] device: auto-accepting incoming (resume after hold)');
                            window._twilioAutoAcceptNextIncoming = false;
                            window._twilioIncomingCall = call;
                            this.acceptIncoming();
                            return;
                        }

                        window._twilioIncomingCall = call;
                        this.hasIncomingCall = true;
                        this.incomingCallerNumber = call.parameters.From || '';
                        // Caller hung up (or <Dial> timed out) before agent answered
                        const onPreAcceptEnd = () => {
                            if (this.hasIncomingCall) {
                                console.log('[AgentPhone] incoming call cancelled/disconnected before answer');
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

                async acceptIncoming() {
                    if (!window._twilioIncomingCall) {
                        console.warn('[AgentPhone] acceptIncoming: no incoming call to accept');
                        return;
                    }
                    const callSid = window._twilioIncomingCall.parameters?.CallSid;
                    const fromNumber = window._twilioIncomingCall.parameters?.From;
                    console.log('[AgentPhone] acceptIncoming', {
                        callSid,
                        fromNumber
                    });
                    window._twilioIncomingCall.accept();
                    window._twilioActiveCall = window._twilioIncomingCall;
                    window._twilioIncomingCall = null;
                    window._twilioHeldCallSid = null;
                    this.hasIncomingCall = false;
                    this.hasActiveCall = true;
                    this.isMuted = false;
                    this.isOnHold = false;
                    this.callStatus = 'In call…';
                    this.attachCallEvents(window._twilioActiveCall);
                    // Navigate to the conversation page.
                    // Pass both CallSid and From so the server can find the record
                    // even when the agent receives a child call SID.
                    // Retry up to 6 times (delays: 0, 500, 1000, 1500, 2000, 2500 ms)
                    // to handle any webhook processing lag.
                    const params = new URLSearchParams();
                    if (callSid) params.set('call_sid', callSid);
                    if (fromNumber) params.set('from', fromNumber);
                    if (params.toString()) {
                        const delay = ms => new Promise(r => setTimeout(r, ms));
                        for (let attempt = 0; attempt < 6; attempt++) {
                            if (attempt > 0) await delay(500 * attempt);
                            console.log('[AgentPhone] acceptIncoming: conversation lookup attempt', attempt, params
                                .toString());
                            try {
                                const res = await fetch(
                                    `/api/call/conversation?${params}`, {
                                        credentials: 'same-origin'
                                    }
                                );
                                if (res.ok) {
                                    const data = await res.json();
                                    console.log('[AgentPhone] acceptIncoming: lookup response', data);
                                    if (data.url) {
                                        console.log('[AgentPhone] acceptIncoming: navigating to', data.url);
                                        if (window.Livewire?.navigate) {
                                            window.Livewire.navigate(data.url);
                                        } else {
                                            window.location.href = data.url;
                                        }
                                        return;
                                    }
                                } else {
                                    console.warn('[AgentPhone] acceptIncoming: non-OK response', res.status);
                                }
                            } catch (e) {
                                console.error('[AgentPhone] acceptIncoming: fetch error on attempt', attempt, e);
                            }
                        }
                        console.error('[AgentPhone] acceptIncoming: conversation not found after retries', {
                            callSid,
                            fromNumber
                        });
                        window.Toast?.show('Call connected — conversation could not be located.', 'warning');
                    }
                },

                rejectIncoming() {
                    if (!window._twilioIncomingCall) {
                        console.warn('[AgentPhone] rejectIncoming: no incoming call');
                        return;
                    }
                    console.log('[AgentPhone] rejectIncoming', {
                        callSid: window._twilioIncomingCall.parameters?.CallSid,
                        from: window._twilioIncomingCall.parameters?.From,
                    });
                    window._twilioIncomingCall.reject();
                    window._twilioIncomingCall = null;
                    this.hasIncomingCall = false;
                },

                // ── outbound ────────────────────────────────────────────

                openDialer() {
                    console.log('[AgentPhone] openDialer', {
                        deviceReady: this.deviceReady
                    });
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
                    console.log('[AgentPhone] makeCall', {
                        number,
                        identity: this.identity,
                        deviceReady: this.deviceReady,
                        hasDevice: !!window._twilioDevice,
                    });
                    if (!window._twilioDevice || !number) {
                        console.warn('[AgentPhone] makeCall: aborted — missing device or number', {
                            hasDevice: !!window._twilioDevice,
                            number
                        });
                        return;
                    }

                    this.callStatus = 'Dialling…';
                    this.isMuted = false;
                    this.isOnHold = false;
                    const agent = this.identity;
                    setTimeout(async () => {
                        try {
                            console.log('[AgentPhone] makeCall: device.connect() params', {
                                To: number,
                                agent
                            });
                            const call = await window._twilioDevice.connect({
                                params: {
                                    To: number,
                                    agent: agent,
                                    From: '{{ config('services.twilio.caller_id') }}',
                                }
                            });
                            console.log('[AgentPhone] makeCall: connected', {
                                callSid: call.parameters?.CallSid
                            });
                            window._twilioActiveCall = call;
                            this.hasActiveCall = true;
                            this.attachCallEvents(call);
                        } catch (e) {
                            console.error('[AgentPhone] makeCall: connect() failed', e);
                            this.callStatus = 'Connecting…';
                            window.Toast.show('Call failed. Please try again.', 'error');
                        }
                    }, 0);
                },

                // ── call events ─────────────────────────────────────────

                attachCallEvents(call) {
                    console.log('[AgentPhone] attachCallEvents', {
                        callSid: call.parameters?.CallSid
                    });
                    call.on('accept', () => {
                        console.log('[AgentPhone] call event: accept', {
                            callSid: call.parameters?.CallSid
                        });
                        this.callStatus = 'In call';
                        this._startTimer();
                    });
                    call.on('disconnect', () => {
                        console.log('[AgentPhone] call event: disconnect', {
                            callSid: call.parameters?.CallSid
                        });
                        this._onCallEnded();
                    });
                    call.on('cancel', () => {
                        console.log('[AgentPhone] call event: cancel', {
                            callSid: call.parameters?.CallSid
                        });
                        this._onCallEnded();
                    });
                    call.on('reject', () => {
                        console.log('[AgentPhone] call event: reject', {
                            callSid: call.parameters?.CallSid
                        });
                        this._onCallEnded();
                    });
                    // Listen for do-transfer dispatched from the transfer modal
                    window.addEventListener('do-transfer', e => this.transferCall(e.detail), {
                        once: true
                    });
                    window.addEventListener('do-whisper', e => this.startWhisper(e.detail), {
                        once: true
                    });
                },

                attachConsultCallEvents(call) {
                    console.log('[AgentPhone] attachConsultCallEvents', {
                        callSid: call.parameters?.CallSid
                    });
                    call.on('accept', () => {
                        console.log('[AgentPhone] consult call event: accept', {
                            callSid: call.parameters?.CallSid
                        });
                        this.hasConsultCall = true;
                        this.callStatus = 'Private consult';
                    });
                    call.on('disconnect', () => {
                        console.log('[AgentPhone] consult call event: disconnect');
                        this._onConsultEnded();
                    });
                    call.on('cancel', () => {
                        console.log('[AgentPhone] consult call event: cancel');
                        this._onConsultEnded();
                    });
                    call.on('reject', () => {
                        console.log('[AgentPhone] consult call event: reject');
                        this._onConsultEnded();
                    });
                },

                // Central teardown called by disconnect / cancel / reject events.
                _onCallEnded() {
                    console.log('[AgentPhone] _onCallEnded', {
                        holdingForConsult: this._holdingForConsult,
                        heldCallSid: window._twilioHeldCallSid,
                        hasConsultCall: this.hasConsultCall,
                    });
                    window._twilioActiveCall = null;
                    if (this._holdingForConsult || window._twilioHeldCallSid) {
                        this._holdingForConsult = false;
                        this.hasActiveCall = false;
                        this.isMuted = false;
                        this.isOnHold = true;
                        this.callStatus = this.hasConsultCall ? 'Private consult' : 'Customer on hold';
                        console.log(
                            '[AgentPhone] _onCallEnded: call ended while holding for consult, preserving hold state');
                        return;
                    }
                    window._twilioCallStartedAt = null; // clear start time only on actual call end
                    this.hasActiveCall = false;
                    this.isMuted = false;
                    this.isOnHold = false;
                    this.callStatus = 'Connecting…';
                    this._stopTimer();
                    console.log('[AgentPhone] _onCallEnded: call fully ended, dispatching call-ended');
                    // Notify DialerPanel that call has ended
                    window.dispatchEvent(new CustomEvent('call-ended'));
                },

                // ── mute ────────────────────────────────────────────────

                async _onConsultEnded() {
                    console.log('[AgentPhone] _onConsultEnded', {
                        heldCallSid: window._twilioHeldCallSid,
                        hasActiveCall: this.hasActiveCall,
                    });
                    window._twilioConsultCall = null;
                    this.hasConsultCall = false;

                    if (window._twilioHeldCallSid) {
                        console.log('[AgentPhone] _onConsultEnded: resuming held customer', window._twilioHeldCallSid);
                        try {
                            await this.resumeHeldCustomer();
                            this.callStatus = 'Reconnecting...';
                        } catch (e) {
                            console.error('[AgentPhone] _onConsultEnded: resume after whisper failed', e);
                            this.callStatus = 'Customer on hold';
                            window.Toast.show('Consult ended, but the client could not be resumed automatically.',
                                'warning');
                        }
                        return;
                    }

                    this.callStatus = this.hasActiveCall ? 'In call' : 'Connecting...';
                    console.log('[AgentPhone] _onConsultEnded: done, callStatus =', this.callStatus);
                },

                toggleMute() {
                    if (!window._twilioActiveCall) {
                        console.warn('[AgentPhone] toggleMute: no active call');
                        return;
                    }
                    this.isMuted = !this.isMuted;
                    console.log('[AgentPhone] toggleMute →', this.isMuted ? 'muted' : 'unmuted');
                    window._twilioActiveCall.mute(this.isMuted);
                    this.callStatus = this.isMuted ? 'Muted' : 'In call';
                },

                // ── hold ────────────────────────────────────────────────

                async toggleHold() {
                    const callSid = window._twilioHeldCallSid || window._twilioActiveCall?.parameters?.CallSid;
                    console.log('[AgentPhone] toggleHold', {
                        isOnHold: this.isOnHold,
                        callSid
                    });
                    if (!callSid) {
                        console.warn('[AgentPhone] toggleHold: no call SID available');
                        window.Toast.show('Call SID not available yet.', 'warning');
                        return;
                    }
                    try {
                        if (!this.isOnHold) {
                            console.log('[AgentPhone] toggleHold: placing on hold…');
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
                            window._twilioHeldCallSid = callSid;
                            this.isOnHold = true;
                            this.callStatus = 'On hold';
                            console.log('[AgentPhone] toggleHold: on hold ✓', callSid);
                        } else {
                            console.log('[AgentPhone] toggleHold: resuming…');
                            await this.resumeHeldCustomer();
                            this.callStatus = 'Reconnecting...';
                            console.log('[AgentPhone] toggleHold: resumed ✓');
                        }
                    } catch (e) {
                        console.error('[AgentPhone] toggleHold: hold/resume failed', e);
                        window.Toast.show('Could not update hold state. Please try again.', 'error');
                    }
                },

                // ── transfer ────────────────────────────────────────────

                async resumeHeldCustomer() {
                    const callSid = window._twilioHeldCallSid || window._twilioActiveCall?.parameters?.CallSid;
                    console.log('[AgentPhone] resumeHeldCustomer', {
                        callSid,
                        identity: this.identity
                    });
                    if (!callSid) {
                        throw new Error('Held call SID not available.');
                    }

                    window._twilioAutoAcceptNextIncoming = true;
                    console.log('[AgentPhone] resumeHeldCustomer: posting resume, autoAccept=true');
                    await fetch('{{ route('twilio.resumeCall') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? ''
                        },
                        body: JSON.stringify({
                            call_sid: callSid,
                            agent_identity: this.identity
                        }),
                        credentials: 'same-origin',
                    });
                    this.isOnHold = false;
                    console.log('[AgentPhone] resumeHeldCustomer: done, isOnHold=false');
                },

                openTransfer() {
                    console.log('[AgentPhone] openTransfer');
                    window.dispatchEvent(new CustomEvent('open-transfer'));
                },

                openWhisper() {
                    const callSid = window._twilioHeldCallSid || window._twilioActiveCall?.parameters?.CallSid;
                    console.log('[AgentPhone] openWhisper', {
                        hasConsultCall: this.hasConsultCall,
                        callSid
                    });
                    if (this.hasConsultCall) {
                        window.Toast.show('A whisper consult is already active.', 'warning');
                        return;
                    }
                    if (!callSid) {
                        console.warn('[AgentPhone] openWhisper: no active call SID');
                        window.Toast.show('An active client call is required first.', 'warning');
                        return;
                    }
                    window.dispatchEvent(new CustomEvent('open-whisper'));
                },

                async startWhisper(destination) {
                    const liveCallSid = window._twilioActiveCall?.parameters?.CallSid;
                    const heldCallSid = window._twilioHeldCallSid;
                    const customerCallSid = heldCallSid || liveCallSid;
                    console.log('[AgentPhone] startWhisper', {
                        destination,
                        customerCallSid,
                        liveCallSid,
                        heldCallSid,
                        identity: this.identity
                    });
                    if (!destination) return;
                    if (!window._twilioDevice) {
                        console.warn('[AgentPhone] startWhisper: device not ready');
                        window.Toast.show('Calling device is not ready yet.', 'warning');
                        return;
                    }
                    if (destination === this.identity) {
                        console.warn('[AgentPhone] startWhisper: cannot whisper to self');
                        window.Toast.show('Select another agent for the consult.', 'warning');
                        return;
                    }
                    if (this.hasConsultCall) {
                        console.warn('[AgentPhone] startWhisper: consult already active');
                        window.Toast.show('A whisper consult is already active.', 'warning');
                        return;
                    }

                    if (!customerCallSid) {
                        console.warn('[AgentPhone] startWhisper: no customer call SID');
                        window.Toast.show('An active client call is required first.', 'warning');
                        return;
                    }

                    try {
                        if (!heldCallSid) {
                            console.log('[AgentPhone] startWhisper: placing customer on hold before consult…');
                            this._holdingForConsult = true;
                            await fetch('{{ route('twilio.holdCall') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ??
                                        ''
                                },
                                body: JSON.stringify({
                                    call_sid: customerCallSid
                                }),
                                credentials: 'same-origin',
                            });
                            window._twilioHeldCallSid = customerCallSid;
                            this.isOnHold = true;
                            this.callStatus = 'Customer on hold';
                            console.log('[AgentPhone] startWhisper: customer on hold ✓', customerCallSid);
                            await new Promise(resolve => setTimeout(resolve, 300));
                        }

                        console.log('[AgentPhone] startWhisper: connecting consult call to', destination);
                        const consultCall = await window._twilioDevice.connect({
                            params: {
                                To: destination,
                                agent: this.identity,
                                whisper_consult: '1',
                                consult_call_sid: customerCallSid,
                                From: '{{ config('services.twilio.caller_id') }}',
                            }
                        });
                        console.log('[AgentPhone] startWhisper: consult call connected', {
                            callSid: consultCall.parameters?.CallSid
                        });
                        window._twilioConsultCall = consultCall;
                        this.hasConsultCall = true;
                        this.callStatus = 'Consulting...';
                        this.attachConsultCallEvents(consultCall);
                    } catch (e) {
                        console.error('[AgentPhone] startWhisper: whisper consult failed', e);
                        if (window._twilioHeldCallSid && !this.hasConsultCall) {
                            console.log('[AgentPhone] startWhisper: attempting to resume held customer after failure…');
                            try {
                                await this.resumeHeldCustomer();
                            } catch (resumeError) {
                                console.error('[AgentPhone] startWhisper: resume after whisper failure also failed',
                                    resumeError);
                            }
                        }
                        window.Toast.show('Whisper consult failed. The client was resumed if possible.', 'error');
                    }
                },

                async transferCall(destination) {
                    const callSid = window._twilioActiveCall?.parameters?.CallSid;
                    console.log('[AgentPhone] transferCall', {
                        destination,
                        callSid
                    });
                    if (!window._twilioActiveCall || !destination) {
                        console.warn('[AgentPhone] transferCall: aborted — no active call or destination');
                        return;
                    }
                    if (!callSid) {
                        console.warn('[AgentPhone] transferCall: no call SID');
                        window.Toast.show('Call SID not available — cannot transfer.', 'warning');
                        return;
                    }
                    try {
                        this.callStatus = 'Transferring…';
                        console.log('[AgentPhone] transferCall: posting transfer…');
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
                        console.log('[AgentPhone] transferCall: transfer posted ✓, hanging up agent leg');
                        // Hang up the agent's leg — caller is now ringing the transfer target
                        this.hangUp();
                        window.Toast.show('Call transferred successfully.', 'success');
                    } catch (e) {
                        console.error('[AgentPhone] transferCall: transfer failed', e);
                        this.callStatus = 'In call';
                        window.Toast.show('Transfer failed. Please try again.', 'error');
                    }
                },

                // ── hang up ─────────────────────────────────────────────

                hangUp() {
                    console.log('[AgentPhone] hangUp', {
                        hasConsultCall: this.hasConsultCall,
                        hasActiveCall: this.hasActiveCall,
                        heldCallSid: window._twilioHeldCallSid,
                    });
                    if (window._twilioConsultCall) {
                        console.log('[AgentPhone] hangUp: disconnecting consult call first');
                        window._twilioConsultCall.disconnect();
                        return;
                    }
                    if (window._twilioActiveCall) {
                        console.log('[AgentPhone] hangUp: disconnecting active call');
                        window._twilioActiveCall.disconnect();
                        window._twilioActiveCall = null;
                        window._twilioCallStartedAt = null;
                        this.hasActiveCall = false;
                        this.isMuted = false;
                        this.isOnHold = false;
                        window._twilioHeldCallSid = null;
                        this._stopTimer();
                        console.log('[AgentPhone] hangUp: done');
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
