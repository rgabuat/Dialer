<div class="space-y-4">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Phone Settings</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Configure call forwarding, voicemail, and do-not-disturb options.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-4">

        {{-- Call Forwarding --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Call Forwarding</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">
                    When enabled, inbound calls are redirected to the number below instead of ringing your browser.
                </p>
            </div>
            <div class="divide-y divide-zinc-800">

                {{-- Enable toggle --}}
                <div class="flex justify-between items-center px-6 py-4">
                    <div>
                        <p class="text-fg-2 text-sm">Enable Call Forwarding</p>
                        <p class="mt-0.5 text-zinc-500 text-xs">Forward all incoming calls to an external number.</p>
                    </div>
                    <label class="inline-flex relative items-center ml-6 cursor-pointer shrink-0">
                        <input type="checkbox" wire:model.live="call_forwarding_enabled" class="sr-only peer">
                        <div
                            class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-11 after:w-5 h-6 after:h-5 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-5 duration-200 after:duration-200">
                        </div>
                    </label>
                </div>

                {{-- Forward-to number --}}
                <div class="px-6 py-4" x-data x-show="$wire.call_forwarding_enabled" x-cloak>
                    <label class="block mb-1.5 font-medium text-fg-muted text-xs">Forward to Number</label>
                    <input wire:model.defer="call_forward_to" type="tel" placeholder="+1 (000) 000-0000"
                        class="w-full max-w-xs bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition placeholder-fg-muted" />
                    @error('call_forward_to')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-zinc-500 text-xs">
                        Calls will be forwarded to this number using your Twilio Caller ID.
                        The forwarding TwiML URL is
                        <code class="text-indigo-400 text-xs">{{ route('twilio.forwardTwiml') }}</code>.
                    </p>
                </div>

            </div>
        </div>

        {{-- Do Not Disturb --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Do Not Disturb</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Block inbound calls from ringing your device.</p>
            </div>
            <div class="flex justify-between items-center px-6 py-4">
                <div>
                    <p class="text-fg-2 text-sm">Enable Do Not Disturb</p>
                    <p class="mt-0.5 text-zinc-500 text-xs">
                        Your status will still show as available, but inbound calls won't ring you.
                    </p>
                </div>
                <label class="inline-flex relative items-center ml-6 cursor-pointer shrink-0">
                    <input type="checkbox" wire:model.defer="do_not_disturb" class="sr-only peer">
                    <div
                        class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-11 after:w-5 h-6 after:h-5 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-5 duration-200 after:duration-200">
                    </div>
                </label>
            </div>
        </div>

        {{-- Voicemail (placeholder) --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden opacity-60 pointer-events-none select-none"
            title="Coming soon">
            <div class="px-6 py-4 border-surface border-b flex items-center gap-2">
                <h2 class="font-semibold text-fg text-sm">Voicemail</h2>
                <span class="text-xs bg-zinc-700 text-zinc-400 rounded-full px-2 py-0.5">Coming soon</span>
            </div>
            <div class="divide-y divide-zinc-800">
                <div class="flex justify-between items-center px-6 py-4">
                    <div>
                        <p class="text-fg-2 text-sm">Enable Voicemail</p>
                        <p class="mt-0.5 text-zinc-500 text-xs">Send unanswered calls to voicemail.</p>
                    </div>
                    <label class="inline-flex relative items-center ml-6 cursor-not-allowed shrink-0">
                        <input type="checkbox" wire:model.defer="voicemail_enabled" disabled class="sr-only peer">
                        <div
                            class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white after:shadow rounded-full after:rounded-full w-11 after:w-5 h-6 after:h-5 after:content-[''] transition-colors after:transition-transform duration-200 after:duration-200">
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Save --}}
        <div class="flex justify-end pt-1">
            <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-lg text-sm font-semibold transition shadow-sm">
                <x-heroicon-o-check class="w-4 h-4" />
                Save Phone Settings
            </button>
        </div>

    </form>

</div>
