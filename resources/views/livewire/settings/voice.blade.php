<div class="space-y-4">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Voice Settings</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Configure call behaviour, TTS messages, and recording options.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-4">

        {{-- ── TTS Messages per Dial Status ────────────────────────────────── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">End-of-Call Messages</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">
                    What the caller hears after the agent hangs up, based on the Twilio dial status.
                </p>
            </div>

            <div class="divide-y divide-zinc-800">

                @foreach ([['completed', 'completed', 'Call was answered and completed normally.'], ['busy', 'busy', 'Agent line was busy — caller was not connected.'], ['no_answer', 'no-answer', 'No agents answered within the timeout window.'], ['failed', 'failed', 'A technical error prevented the call from connecting.'], ['canceled', 'canceled', 'Caller hung up before an agent answered.']] as [$prop, $status, $desc])
                    <div class="px-6 py-4 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide
                                {{ $status === 'completed' ? 'bg-emerald-900/60 text-emerald-400' : 'bg-amber-900/60 text-amber-400' }}">
                                {{ $status }}
                            </span>
                            <span class="text-zinc-500 text-xs">{{ $desc }}</span>
                        </div>
                        <input wire:model.defer="tts_{{ $prop }}" type="text"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition placeholder-fg-muted"
                            placeholder="Message spoken to the caller…" />
                        @error('tts_' . $prop)
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

            </div>
        </div>

        {{-- ── TTS Voice & Language ─────────────────────────────────────────── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Text-to-Speech Voice</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Applied to all TTS messages above (requires Twilio Standard or
                    Plus).</p>
            </div>
            <div class="grid grid-cols-2 divide-x divide-zinc-800">
                <div class="px-6 py-4 space-y-1.5">
                    <label class="block text-fg-muted text-xs font-medium">Voice</label>
                    <select wire:model.defer="tts_voice"
                        class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition">
                        <option value="alice">Alice (Twilio's default)</option>
                        <option value="woman">Woman</option>
                        <option value="man">Man</option>
                    </select>
                    @error('tts_voice')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="px-6 py-4 space-y-1.5">
                    <label class="block text-fg-muted text-xs font-medium">Language</label>
                    <select wire:model.defer="tts_language"
                        class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition">
                        <option value="en-US">English (US)</option>
                        <option value="en-GB">English (UK)</option>
                        <option value="es-US">Spanish (US)</option>
                        <option value="es-ES">Spanish (Spain)</option>
                        <option value="fr-FR">French</option>
                        <option value="de-DE">German</option>
                        <option value="pt-BR">Portuguese (Brazil)</option>
                    </select>
                    @error('tts_language')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ── Inbound Routing ──────────────────────────────────────────────── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Inbound Routing</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Control how inbound calls are handled before reaching an agent.
                </p>
            </div>
            <div class="divide-y divide-zinc-800">

                {{-- Inbound timeout --}}
                <div class="px-6 py-4 space-y-1.5">
                    <label class="block text-fg-muted text-xs font-medium">Agent Ring Timeout (seconds)</label>
                    <p class="text-zinc-500 text-xs">How long to ring available agents before giving up and playing the
                        no-answer message.</p>
                    <input wire:model.defer="inbound_timeout" type="number" min="5" max="120"
                        step="5"
                        class="w-32 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition" />
                    @error('inbound_timeout')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Greeting message --}}
                <div class="px-6 py-4 space-y-1.5">
                    <label class="block text-fg-muted text-xs font-medium">Caller Greeting <span
                            class="text-zinc-600 font-normal">(optional)</span></label>
                    <p class="text-zinc-500 text-xs">Played to the caller while their call is being connected to an
                        agent. Leave blank to skip.</p>
                    <input wire:model.defer="greeting_message" type="text"
                        class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition placeholder-fg-muted"
                        placeholder="e.g. Thank you for calling, please hold while we connect you…" />
                    @error('greeting_message')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ── Hold Music ───────────────────────────────────────────────────── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Hold Music</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Audio played on loop when a call is placed on hold. Must be a
                    publicly accessible MP3/WAV URL.</p>
            </div>
            <div class="px-6 py-4 space-y-1.5">
                <label class="block text-fg-muted text-xs font-medium">Hold Music URL</label>
                <input wire:model.defer="hold_music_url" type="url"
                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition placeholder-fg-muted"
                    placeholder="https://example.com/hold-music.mp3" />
                @error('hold_music_url')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ── Call Recording ───────────────────────────────────────────────── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Call Recording</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Automatically record calls. Recordings are stored in your Twilio
                    account.</p>
            </div>
            <div class="divide-y divide-zinc-800">

                {{-- Enable toggle --}}
                <div class="flex justify-between items-center px-6 py-4">
                    <div>
                        <p class="text-fg-2 text-sm">Enable Call Recording</p>
                        <p class="mt-0.5 text-zinc-500 text-xs">All inbound and outbound calls will be recorded.</p>
                    </div>
                    <label class="inline-flex relative items-center ml-6 cursor-pointer shrink-0">
                        <input type="checkbox" wire:model.live="recording_enabled" class="sr-only peer">
                        <div
                            class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-11 after:w-5 h-6 after:h-5 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-5 duration-200 after:duration-200">
                        </div>
                    </label>
                </div>

                {{-- Recording channels --}}
                <div class="px-6 py-4 space-y-1.5" x-data x-show="$wire.recording_enabled" x-cloak>
                    <label class="block text-fg-muted text-xs font-medium">Record Channels</label>
                    <p class="text-zinc-500 text-xs">Which audio to include in the recording.</p>
                    <select wire:model.defer="recording_channels"
                        class="w-48 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition">
                        <option value="both">Both (agent + caller)</option>
                        <option value="inbound">Inbound only (caller)</option>
                        <option value="outbound">Outbound only (agent)</option>
                    </select>
                    @error('recording_channels')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Save --}}
        <div class="flex justify-end pt-1">
            <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-lg text-sm font-semibold transition shadow-sm">
                <x-heroicon-o-check class="w-4 h-4" />
                Save Voice Settings
            </button>
        </div>

    </form>

</div>
