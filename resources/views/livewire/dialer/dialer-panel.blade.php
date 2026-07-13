{{--
    Dialer Panel — shown inside the agent control bar area.
    Handles MANUAL / PREVIEW / PROGRESSIVE / PREDICTIVE modes.
    JS events communicate with window._twilioDevice (set in agent-control-bar.blade.php).
--}}
<div x-data="{
    dialMode: '{{ $campaign?->dial_mode ?? 'MANUAL' }}',
    campaignType: '{{ $campaign?->type ?? '' }}',
    previewPhone: null,
    previewLeadId: null,
    previewHopperId: null,
    isDialing: false,
    statusHandlesOutbound: false,

    init() {
        // Listen for status changes from StatusSwitcher
        window.addEventListener('agent-status-changed', (e) => {
            const d = Array.isArray(e.detail) ? (e.detail[0] ?? {}) : (e.detail ?? {});
            this.statusHandlesOutbound = d.handles_outbound ?? false;
            $wire.call('handleStatusChange', d);
        });

        // Listen for PREVIEW data from Livewire
        $wire.on('dialer-preview', (data) => {
            this.previewPhone = data.phone;
            this.previewLeadId = data.leadId;
            this.previewHopperId = data.hopperId;
        });

        // Listen for no-leads signal
        $wire.on('dialer-no-leads', () => {
            alert('No more leads in the hopper.');
        });
    },

    // MANUAL: agent types number and clicks dial
    dialManual(phone) {
        if (!phone) return;
        this.isDialing = true;
        window.dispatchEvent(new CustomEvent('make-call', { detail: { phone } }));
    },

    // PREVIEW: agent confirms the pre-loaded lead
    dialPreview() {
        if (!this.previewPhone) return;
        this.isDialing = true;
        window.dispatchEvent(new CustomEvent('make-call', { detail: { phone: this.previewPhone } }));
        this.previewPhone = null;
    },

    // PROGRESSIVE/PREDICTIVE: ask server to autodial
    async dialAuto() {
        this.isDialing = true;
        try {
            const resp = await fetch('/api/dialer/autodial', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ campaign_id: {{ $campaignId ?? 'null' }} }),
            });
            if (!resp.ok) {
                const err = await resp.json();
                alert(err.message ?? 'Dial failed.');
                this.isDialing = false;
            }
        } catch (e) {
            console.error('[Dialer] autodial error', e);
            this.isDialing = false;
        }
    },

    hangup() {
        window.dispatchEvent(new CustomEvent('hangup-call'));
        this.isDialing = false;
        this.previewPhone = null;
    }
}" @call-ended.window="isDialing = false; previewPhone = null"
    class="bg-surface border border-surface rounded-xl p-4 space-y-3" wire:poll.30s="refreshHopperCount">
    {{-- No campaign selected --}}
    @if (!$campaign)
        <div class="flex items-center gap-2 text-fg-muted text-sm">
            <x-heroicon-o-megaphone class="w-4 h-4 shrink-0" />
            <span>Select a campaign to use the dialer.</span>
        </div>
    @else
        {{-- Campaign header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-heroicon-s-megaphone class="w-4 h-4 text-fuchsia-400 shrink-0" />
                <span class="font-semibold text-fg text-sm truncate">{{ $campaign->name }}</span>
                <span class="px-1.5 py-0.5 rounded text-xs font-mono bg-surface-2 text-fg-muted">
                    {{ $campaign->dial_mode }}
                </span>
            </div>
            {{-- Hopper count (PROGRESSIVE/PREDICTIVE only) --}}
            @if (in_array($campaign->dial_mode, ['PROGRESSIVE', 'PREDICTIVE']))
                <div class="flex items-center gap-1.5">
                    <span class="text-fg-muted text-xs">Hopper:</span>
                    <span class="font-mono font-bold text-fuchsia-400 text-sm">{{ $hopperCount ?? 0 }}</span>
                    <button wire:click="fillHopper" title="Refill hopper"
                        class="flex items-center justify-center w-5 h-5 rounded hover:bg-surface-2 text-fg-muted hover:text-fg transition">
                        <x-heroicon-o-arrow-path class="w-3 h-3" />
                    </button>
                </div>
            @endif
        </div>

        {{-- ── MANUAL dial pad ── --}}
        @if ($campaign->dial_mode === 'MANUAL')
            <div class="flex items-center gap-2" x-data="{ phone: '' }">
                <input x-model="phone" type="tel" placeholder="+1 555 000 0000"
                    class="flex-1 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                <button @click="dialManual(phone)" :disabled="isDialing || !phone.trim()"
                    :class="isDialing || !phone.trim() ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-500'"
                    class="flex items-center justify-center w-9 h-9 rounded-lg bg-accent-green transition">
                    <x-heroicon-s-phone class="w-4 h-4 text-white" />
                </button>
                <button x-show="isDialing" @click="hangup()"
                    class="flex items-center justify-center w-9 h-9 rounded-lg bg-accent-red hover:bg-red-400 transition">
                    <x-heroicon-s-phone-x-mark class="w-4 h-4 text-white" />
                </button>
            </div>
        @endif

        {{-- ── PREVIEW mode ── --}}
        @if ($campaign->dial_mode === 'PREVIEW')
            <div class="space-y-2">
                <template x-if="!previewPhone">
                    <button wire:click="previewNext"
                        class="w-full inline-flex justify-center items-center gap-2 bg-fuchsia-600 hover:bg-fuchsia-500 px-4 py-2 rounded-lg font-semibold text-white text-sm transition">
                        <x-heroicon-o-arrow-right class="w-4 h-4" />
                        Next Lead
                    </button>
                </template>
                <template x-if="previewPhone">
                    <div class="bg-surface-2 border border-surface rounded-xl p-3 space-y-2">
                        <p class="text-fg-muted text-xs">Preview</p>
                        <p class="font-mono font-bold text-fg" x-text="previewPhone"></p>
                        <div class="flex gap-2">
                            <button @click="dialPreview()" :disabled="isDialing"
                                class="flex-1 inline-flex justify-center items-center gap-1.5 bg-accent-green hover:bg-green-400 px-3 py-1.5 rounded-lg font-semibold text-white text-sm transition">
                                <x-heroicon-s-phone class="w-3.5 h-3.5" />
                                Dial
                            </button>
                            <button @click="previewPhone = null; previewLeadId = null"
                                class="inline-flex items-center gap-1.5 bg-surface border border-surface px-3 py-1.5 rounded-lg text-fg-muted text-sm hover:text-fg transition">
                                Skip
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        @endif

        {{-- ── PROGRESSIVE / PREDICTIVE ── --}}
        @if (in_array($campaign->dial_mode, ['PROGRESSIVE', 'PREDICTIVE']))
            <div class="flex items-center gap-2">
                <button @click="dialAuto()" :disabled="isDialing"
                    :class="isDialing ? 'opacity-50 cursor-not-allowed' : 'hover:bg-fuchsia-500'"
                    class="flex-1 inline-flex justify-center items-center gap-2 bg-fuchsia-600 px-4 py-2 rounded-lg font-semibold text-white text-sm transition">
                    <span x-show="!isDialing" class="flex items-center gap-2">
                        <x-heroicon-s-phone class="w-4 h-4" />
                        {{ $campaign->dial_mode === 'PREDICTIVE' ? 'Auto Dial' : 'Dial Next' }}
                    </span>
                    <span x-show="isDialing" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        Dialing…
                    </span>
                </button>
                <button x-show="isDialing" @click="hangup()"
                    class="flex items-center justify-center w-9 h-9 rounded-lg bg-accent-red hover:bg-red-400 transition">
                    <x-heroicon-s-phone-x-mark class="w-4 h-4 text-white" />
                </button>
            </div>

            {{-- Dial level (PREDICTIVE only) --}}
            @if ($campaign->dial_mode === 'PREDICTIVE')
                <div class="flex items-center gap-2 text-xs text-fg-muted">
                    <span>Dial ratio:</span>
                    <span class="font-mono text-fg">{{ number_format($campaign->dial_level, 2) }}x</span>
                </div>
            @endif
        @endif

        {{-- Session flash --}}
        @if (session('hopper_msg'))
            <p class="text-accent-green text-xs">{{ session('hopper_msg') }}</p>
        @endif
    @endif
</div>
