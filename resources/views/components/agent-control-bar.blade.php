{{-- Inline agent controls – rendered inside the topbar right section --}}
<div x-data="agentPhone()"
     @make-call.window="makeCall($event.detail)"
     @agent-status-changed.window="canAcceptCalls = ($event.detail.isAvailable ?? canAcceptCalls)"
     class="flex items-center gap-3">

    {{-- LEFT GROUP: Call controls --}}
    <div class="flex items-center gap-1.5 px-2 py-1 border border-surface-2 rounded-lg">

        {{-- ENABLE CALLING --}}
        <button @click="enableCalling" :disabled="!canAcceptCalls"
            class="px-3 py-1.5 rounded-md font-semibold text-xs transition"
            :class="canAcceptCalls
                ? 'bg-blue-600 text-white hover:bg-blue-500'
                : 'bg-surface-2 text-zinc-500 cursor-not-allowed'">
            Enable Calling
        </button>

        {{-- DIAL --}}
        <button @click="openDialer" :disabled="!deviceReady"
            class="px-3 py-1.5 rounded-md font-semibold text-xs transition"
            :class="deviceReady
                ? 'bg-zinc-700 text-fg-2 hover:bg-zinc-600'
                : 'bg-surface-2 text-zinc-500 cursor-not-allowed'">
            Dial
        </button>

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
            <button @click="$dispatch('make-call', $refs.dialNumber.value)"
                class="bg-green-600 hover:bg-green-500 px-4 py-2 rounded-lg font-semibold text-fg text-sm">Call</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function agentPhone() {
        return {
            token: null,
            identity: null,
            device: null,
            deviceReady: false,
            canAcceptCalls: @json(auth()->user()?->agentStatus?->statusType?->is_available ?? false),

            async enableCalling() {
                if (!this.canAcceptCalls) {
                    window.Toast.show('You are not accepting calls in your current status.', 'warning');
                    return;
                }
                if (this.device) return;
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
                this.device = new window.Device(this.token, {
                    codecPreferences: ['opus', 'pcmu'],
                    logLevel: 1,
                });
                this.device.on('ready', () => { this.deviceReady = true; });
                this.device.on('error', error => {
                    console.error('Twilio error', error);
                    window.Toast.show('Calling device error. Please reload.', 'error');
                });
                this.device.register();
            },

            openDialer() {
                if (!this.deviceReady) {
                    window.Toast.show('Enable calling first.', 'warning');
                    return;
                }
                window.dispatchEvent(new CustomEvent('open-dialer'));
            },

            async makeCall(number) {
                if (!this.device || !number) return;
                await this.device.connect({
                    params: {
                        To:    number,
                        agent: this.identity,
                        From:  '{{ config('services.twilio.caller_id') }}',
                    }
                });
            }
        }
    }
</script>
@endpush
