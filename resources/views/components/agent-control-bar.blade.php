{{-- Inline agent controls – rendered inside the topbar right section --}}
<div x-data="agentPhone()"
     @make-call.window="makeCall($event.detail)"
     @agent-status-changed.window="canAcceptCalls = ($event.detail.isAvailable ?? canAcceptCalls)"
     class="flex items-center gap-3">

    {{-- LEFT GROUP: Call controls --}}
    <div class="flex items-center gap-1.5 px-2 py-1 border border-surface-2 rounded-lg">

        {{-- ENABLE CALLING --}}
        <button x-show="!deviceReady" @click="enableCalling" :disabled="!canAcceptCalls"
            class="px-3 py-1.5 rounded-md font-semibold text-xs transition"
            :class="canAcceptCalls
                ? 'bg-blue-600 text-white hover:bg-blue-500'
                : 'bg-surface-2 text-zinc-500 cursor-not-allowed'">
            Enable Calling
        </button>

        {{-- DIAL --}}
        <button x-show="deviceReady && !hasActiveCall" @click="openDialer"
            class="px-3 py-1.5 rounded-md font-semibold text-xs bg-zinc-700 text-fg-2 hover:bg-zinc-600 transition">
            Dial
        </button>

        {{-- ACTIVE CALL status + HANG UP --}}
        <template x-if="hasActiveCall">
            <div class="flex items-center gap-2">
                <span class="text-xs text-green-400 font-semibold" x-text="callStatus"></span>
                <button @click="hangUp"
                    class="px-3 py-1.5 rounded-md font-semibold text-xs bg-red-600 text-white hover:bg-red-500 transition">
                    Hang Up
                </button>
            </div>
        </template>

    </div>

    {{-- SEPARATOR --}}
    <div class="bg-zinc-700 w-px h-6"></div>

    {{-- RIGHT GROUP: Status + time --}}
    <div class="flex items-center px-2 py-1 border border-surface-2 rounded-lg">
        @livewire('agent.status-switcher')
    </div>

</div>

{{-- DIAL MODAL --}}
<div x-data="{ open: false }" x-on:open-dialer.window="open = true" x-show="open" x-cloak
    class="z-50 fixed inset-0 flex justify-center items-center bg-black/60">
    <div class="bg-surface shadow-xl p-6 border border-surface rounded-xl w-full max-w-sm">
        <h2 class="mb-4 font-semibold text-fg text-lg">Outbound Call</h2>
        <input x-ref="dialNumber" type="tel" placeholder="+1234567890"
            class="bg-surface-2 mb-4 px-4 py-2 border border-surface-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 w-full text-fg placeholder-fg-muted" />
        <div class="flex justify-end gap-3">
            <button @click="open = false"
                class="bg-surface-2 hover:bg-surface px-4 py-2 rounded-lg text-fg-3 text-sm">Cancel</button>
            <button @click="$dispatch('make-call', $refs.dialNumber.value); open = false"
                class="bg-green-600 hover:bg-green-500 px-4 py-2 rounded-lg font-semibold text-fg text-sm">Call</button>
        </div>
    </div>
</div>

{{-- INCOMING CALL MODAL --}}
<div x-data="{ show: false, callerNumber: '' }"
     @incoming-call.window="show = true; callerNumber = $event.detail"
     x-show="show" x-cloak
     class="z-50 fixed inset-0 flex justify-center items-center bg-black/60">
    <div class="bg-surface shadow-xl p-6 border border-surface rounded-xl w-full max-w-sm text-center">
        <div class="mb-1 text-4xl">📞</div>
        <h2 class="mb-1 font-semibold text-fg text-lg">Incoming Call</h2>
        <p class="mb-5 text-fg-3 text-sm" x-text="callerNumber || 'Unknown caller'"></p>
        <div class="flex justify-center gap-4">
            <button @click="$dispatch('reject-call'); show = false"
                class="bg-red-600 hover:bg-red-500 px-6 py-2 rounded-lg font-semibold text-fg text-sm">Reject</button>
            <button @click="$dispatch('accept-call'); show = false"
                class="bg-green-600 hover:bg-green-500 px-6 py-2 rounded-lg font-semibold text-fg text-sm">Accept</button>
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
            canAcceptCalls: @json(auth()->user()?->agentStatus?->statusType?->is_available ?? false),

            async enableCalling() {
                if (!this.canAcceptCalls) {
                    window.Toast.show('You are not accepting calls in your current status.', 'warning');
                    return;
                }
                if (_twilioDevice) return;
                try {
                    const res  = await fetch('{{ route('twilio.getAccessToken') }}');
                    const data = await res.json();
                    this.token    = data.token;
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
                    window.dispatchEvent(new CustomEvent('incoming-call', { detail: from }));

                    const acceptHandler  = () => this.acceptIncoming();
                    const rejectHandler  = () => this.rejectIncoming();
                    window.addEventListener('accept-call', acceptHandler, { once: true });
                    window.addEventListener('reject-call', rejectHandler, { once: true });
                });

                _twilioDevice.register();
            },

            acceptIncoming() {
                if (!_twilioIncomingCall) return;
                _twilioIncomingCall.accept();
                _twilioActiveCall = _twilioIncomingCall;
                _twilioIncomingCall = null;
                this.hasIncomingCall = false;
                this.hasActiveCall = true;
                this.callStatus = 'In call…';
                this.attachCallEvents(_twilioActiveCall);
            },

            rejectIncoming() {
                if (!_twilioIncomingCall) return;
                _twilioIncomingCall.reject();
                _twilioIncomingCall = null;
                this.hasIncomingCall = false;
            },

            openDialer() {
                if (!this.deviceReady) {
                    window.Toast.show('Enable calling first.', 'warning');
                    return;
                }
                window.dispatchEvent(new CustomEvent('open-dialer'));
            },

            async makeCall(number) {
                if (!_twilioDevice || !number) return;
                this.callStatus = 'Dialling…';
                const call = await _twilioDevice.connect({
                    params: {
                        To:    number,
                        agent: this.identity,
                        From:  '{{ config('services.twilio.caller_id') }}',
                    }
                });
                _twilioActiveCall = call;
                this.hasActiveCall = true;
                this.attachCallEvents(call);
            },

            attachCallEvents(call) {
                call.on('accept',     ()  => { this.callStatus = 'In call…'; });
                call.on('disconnect', ()  => {
                    _twilioActiveCall = null;
                    this.hasActiveCall = false;
                    this.callStatus = 'Connecting…';
                });
                call.on('cancel',     ()  => {
                    _twilioActiveCall = null;
                    this.hasActiveCall = false;
                    this.callStatus = 'Connecting…';
                });
                call.on('reject',     ()  => {
                    _twilioActiveCall = null;
                    this.hasActiveCall = false;
                    this.callStatus = 'Connecting…';
                });
            },

            hangUp() {
                if (_twilioActiveCall) {
                    _twilioActiveCall.disconnect();
                    _twilioActiveCall = null;
                    this.hasActiveCall = false;
                }
            },
        }
    }
</script>
@endpush
