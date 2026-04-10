<form wire:submit.prevent="save">
    <div class="min-h-screen bg-surface-4 text-fg p-6">

        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">New Campaign</h1>
            <p class="text-sm text-fg-muted">Create a new campaign and configure its dialer settings.</p>
        </div>

        <div class="max-w-4xl space-y-10">

            {{-- Details --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-fg-muted">Campaign information.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">Description</label>
                        <textarea wire:model.defer="description" rows="3"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                        @error('description')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-fg-muted">Active</label>
                    </div>
                </div>
            </div>

            {{-- Dialer Settings --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Dialer Settings</h2>
                    <p class="text-sm text-fg-muted">Configure how calls are placed and handled.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Campaign Type</label>
                            <select wire:model.defer="type"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="OUTBOUND">Outbound</option>
                                <option value="INBOUND">Inbound</option>
                                <option value="BLENDED">Blended</option>
                            </select>
                            @error('type')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Dial Mode</label>
                            <select wire:model.defer="dial_mode"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="MANUAL">Manual</option>
                                <option value="PREVIEW">Preview</option>
                                <option value="PROGRESSIVE">Progressive</option>
                                <option value="PREDICTIVE">Predictive</option>
                            </select>
                            @error('dial_mode')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Dial Level <span class="text-xs">(predictive
                                    ratio)</span></label>
                            <input wire:model.defer="dial_level" type="number" step="0.1" min="0.1"
                                max="10"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('dial_level')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Max Simultaneous Calls</label>
                            <input wire:model.defer="max_calls" type="number" min="1" max="100"
                                placeholder="Unlimited"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('max_calls')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Outbound Caller ID</label>
                            <input wire:model.defer="caller_id" type="text"
                                placeholder="+1234567890 (leave blank for default)"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('caller_id')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Hopper Level <span class="text-xs">(leads to
                                    pre-queue)</span></label>
                            <input wire:model.defer="hopper_level" type="number" min="1" max="1000"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('hopper_level')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">After-Call Work (ACW) Timer <span class="text-xs">seconds,
                                0 = disabled</span></label>
                        <input wire:model.defer="acw_seconds" type="number" min="0" max="3600"
                            class="mt-1 w-32 rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('acw_seconds')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Script --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Agent Script</h2>
                    <p class="text-sm text-fg-muted">Text or HTML shown to agents during active calls.</p>
                </div>
                <div class="md:col-span-3">
                    <textarea wire:model.defer="script" rows="8" placeholder="Enter the script agents will see during calls..."
                        class="w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-zinc-600"></textarea>
                    @error('script')
                        <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Voice & Recording --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Voice & Recording</h2>
                    <p class="text-sm text-fg-muted">TTS messages, hold music, and call recording settings for this
                        campaign.</p>
                </div>
                <div class="md:col-span-3 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-fg">TTS Voice</label>
                            <select wire:model.defer="tts_voice"
                                class="w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="alice">Alice (neural)</option>
                                <option value="man">Man</option>
                                <option value="woman">Woman</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-fg">Language</label>
                            <select wire:model.defer="tts_language"
                                class="w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="en-US">English (US)</option>
                                <option value="en-GB">English (UK)</option>
                                <option value="es-US">Spanish (US)</option>
                                <option value="es-ES">Spanish (ES)</option>
                                <option value="fr-FR">French</option>
                                <option value="de-DE">German</option>
                                <option value="pt-BR">Portuguese (BR)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-fg">Greeting Message <span
                                    class="font-normal text-fg-muted">(optional)</span></label>
                            <input wire:model.defer="greeting_message" type="text"
                                placeholder="Welcome to Acme support..."
                                class="w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                            <p class="text-xs text-fg-muted mt-1">Played when caller first connects.</p>
                            @error('greeting_message')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-fg">Hold Music URL <span
                                    class="font-normal text-fg-muted">(blank = Twilio default)</span></label>
                            <input wire:model.defer="hold_music_url" type="url" placeholder="https://..."
                                class="w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                            @error('hold_music_url')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-fg mb-2">End-of-Call Messages</p>
                        <div class="space-y-2">
                            @foreach ([['key' => 'tts_completed', 'label' => 'Completed'], ['key' => 'tts_busy', 'label' => 'Busy'], ['key' => 'tts_no_answer', 'label' => 'No Answer'], ['key' => 'tts_failed', 'label' => 'Failed'], ['key' => 'tts_canceled', 'label' => 'Canceled']] as $tts)
                                <div class="flex items-center gap-3">
                                    <span class="text-fg-muted text-xs w-24 shrink-0">{{ $tts['label'] }}</span>
                                    <input wire:model.defer="{{ $tts['key'] }}" type="text"
                                        class="flex-1 rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                    @error($tts['key'])
                                        <p class="text-xs text-accent-red mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 border-t border-surface pt-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="recording_enabled" type="checkbox"
                                class="w-4 h-4 rounded text-blue-600 border-surface focus:ring-zinc-600 focus:ring-offset-0">
                            <span class="text-sm font-medium text-fg">Enable Call Recording</span>
                        </label>
                        @if ($recording_enabled)
                            <select wire:model.defer="recording_channels"
                                class="rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="both">Both channels</option>
                                <option value="inbound">Inbound only</option>
                                <option value="outbound">Outbound only</option>
                            </select>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    Create Campaign
                </button>
                <a href="{{ route('campaigns.index') }}" wire:navigate
                    class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg-muted text-sm font-medium transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
