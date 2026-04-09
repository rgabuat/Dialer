<form wire:submit.prevent="save">
    <div class="bg-surface-4 p-6 min-h-screen text-fg">

        <div class="mb-8">
            <h1 class="font-semibold text-2xl tracking-tight">New In-Group</h1>
            <p class="text-fg-muted text-sm">Create an inbound call queue and configure its routing behaviour.</p>
        </div>

        <div class="space-y-10 max-w-4xl">

            {{-- Details --}}
            <div class="gap-6 grid grid-cols-1 md:grid-cols-4 pb-10 border-surface border-b">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-fg-muted text-sm">Basic info about this in-group.</p>
                </div>
                <div class="space-y-5 md:col-span-3">
                    <div>
                        <label class="text-fg-muted text-sm">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                        @error('name')
                            <p class="mt-1 text-xs text-accent-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-fg-muted text-sm">Description</label>
                        <textarea wire:model.defer="description" rows="2"
                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="bg-surface border-surface-2 rounded focus:ring-blue-500 text-blue-500" />
                        <label for="is_active" class="text-fg-muted text-sm">Active</label>
                    </div>
                    <div>
                        <label class="text-fg-muted text-sm">Campaign (optional — for reporting)</label>
                        <select wire:model.defer="campaign_id"
                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                            <option value="">— None —</option>
                            @foreach ($campaigns as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Routing --}}
            <div class="gap-6 grid grid-cols-1 md:grid-cols-4 pb-10 border-surface border-b">
                <div>
                    <h2 class="font-medium">Routing</h2>
                    <p class="text-fg-muted text-sm">How calls are distributed to available agents.</p>
                </div>
                <div class="space-y-5 md:col-span-3">
                    <div class="gap-4 grid grid-cols-2">
                        <div>
                            <label class="text-fg-muted text-sm">Agent Routing Algorithm</label>
                            <select wire:model.defer="agent_routing"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                <option value="ring_all">Ring All</option>
                                <option value="round_robin">Round Robin</option>
                                <option value="fewest_calls">Fewest Calls</option>
                                <option value="longest_idle">Longest Idle</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-fg-muted text-sm">Queue Priority (higher = first)</label>
                            <input wire:model.defer="queue_priority" type="number" min="1" max="999"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                        </div>
                    </div>
                    <div class="gap-4 grid grid-cols-2">
                        <div>
                            <label class="text-fg-muted text-sm">Max Wait (seconds, blank = unlimited)</label>
                            <input wire:model.defer="max_wait_seconds" type="number" min="1" max="3600"
                                placeholder="20"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                        </div>
                        <div>
                            <label class="text-fg-muted text-sm">Hold Music URL (blank = default)</label>
                            <input wire:model.defer="hold_music_url" type="url" placeholder="https://..."
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="text-fg-muted text-sm">Screen-Pop URL (delivered to agent on answer)</label>
                        <input wire:model.defer="web_form_url" type="url"
                            placeholder="https://crm.example.com/lookup?phone={PHONE}"
                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                    </div>
                    <div class="gap-4 grid grid-cols-2">
                        <div>
                            <label class="text-fg-muted text-sm">Drop Action (no agents available)</label>
                            <select wire:model.defer="drop_action"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                <option value="hangup">Hang Up</option>
                                <option value="transfer">Transfer to Number</option>
                                <option value="voicemail">Voicemail</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-fg-muted text-sm">Drop Destination (if transfer/voicemail)</label>
                            <input wire:model.defer="drop_destination" type="text" placeholder="+15551234567"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- After Hours --}}
            <div class="gap-6 grid grid-cols-1 md:grid-cols-4 pb-10 border-surface border-b">
                <div>
                    <h2 class="font-medium">After Hours</h2>
                    <p class="text-fg-muted text-sm">Define operating hours and what happens outside them.</p>
                </div>
                <div class="space-y-5 md:col-span-3">
                    <div class="flex items-center gap-3">
                        <input wire:model.live="always_open" type="checkbox" id="always_open"
                            class="bg-surface border-surface-2 rounded focus:ring-blue-500 text-blue-500" />
                        <label for="always_open" class="text-fg-muted text-sm">Always open (24/7 — ignore
                            schedule)</label>
                    </div>

                    @if (!$always_open)
                        <div>
                            <label class="text-fg-muted text-sm">Timezone</label>
                            <select wire:model.defer="timezone"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                @foreach ($timezones as $tz)
                                    <option value="{{ $tz }}" @selected($tz === $timezone)>{{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            @foreach (['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'] as $key => $label)
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2 w-28">
                                        <input wire:model.live="hours.{{ $key }}.enabled" type="checkbox"
                                            class="bg-surface border-surface-2 rounded focus:ring-blue-500 text-blue-500" />
                                        <span class="text-fg-muted text-sm">{{ $label }}</span>
                                    </div>
                                    @if ($hours[$key]['enabled'])
                                        <input wire:model.defer="hours.{{ $key }}.open" type="time"
                                            class="bg-surface px-2 py-1 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 text-fg text-sm" />
                                        <span class="text-fg-muted text-sm">to</span>
                                        <input wire:model.defer="hours.{{ $key }}.close" type="time"
                                            class="bg-surface px-2 py-1 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 text-fg text-sm" />
                                    @else
                                        <span class="text-fg-muted text-xs italic">Closed</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="gap-4 grid grid-cols-2">
                            <div>
                                <label class="text-fg-muted text-sm">After-Hours Action</label>
                                <select wire:model.defer="after_hours_action"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                    <option value="hangup">Hang Up</option>
                                    <option value="transfer">Transfer to Number</option>
                                    <option value="voicemail">Voicemail</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-fg-muted text-sm">After-Hours Destination</label>
                                <input wire:model.defer="after_hours_destination" type="text"
                                    placeholder="+15551234567"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                    Create In-Group
                </button>
                <a href="{{ route('in-groups.index') }}" wire:navigate
                    class="bg-surface-2 hover:bg-surface px-4 py-2 rounded-md font-medium text-fg-muted text-sm transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
