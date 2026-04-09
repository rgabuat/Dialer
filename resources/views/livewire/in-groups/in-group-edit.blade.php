<div class="bg-surface-4 p-6 min-h-screen text-fg">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="font-semibold text-2xl tracking-tight">{{ $inGroup->name }}</h1>
            <p class="text-fg-muted text-sm">Edit in-group settings, assign agents, and review linked DIDs.</p>
        </div>
        <a href="{{ route('in-groups.index') }}" wire:navigate class="text-fg-muted hover:text-fg text-sm transition">←
            Back</a>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 mb-6 px-4 py-3 border border-green-500/20 rounded-lg text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-surface border-b">
        @foreach (['settings' => 'Settings', 'agents' => 'Agents', 'dids' => 'DIDs'] as $tab => $label)
            <button wire:click="$set('activeTab', '{{ $tab }}')"
                class="px-4 py-2 text-sm font-medium transition rounded-t-md
                    {{ $activeTab === $tab ? 'text-fg border-b-2 border-blue-500' : 'text-fg-muted hover:text-fg' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Settings Tab --}}
    @if ($activeTab === 'settings')
        <form wire:submit.prevent="save">
            <div class="space-y-10 max-w-4xl">

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
                                    <option value="{{ $c->id }}" @selected($c->id == $campaign_id)>{{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

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
                                <label class="text-fg-muted text-sm">Hold Music URL</label>
                                <input wire:model.defer="hold_music_url" type="url" placeholder="https://..."
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="text-fg-muted text-sm">Screen-Pop URL</label>
                            <input wire:model.defer="web_form_url" type="url"
                                placeholder="https://crm.example.com/lookup?phone={PHONE}"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                        </div>
                        <div class="gap-4 grid grid-cols-2">
                            <div>
                                <label class="text-fg-muted text-sm">Drop Action</label>
                                <select wire:model.defer="drop_action"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                    <option value="hangup">Hang Up</option>
                                    <option value="transfer">Transfer to Number</option>
                                    <option value="voicemail">Voicemail</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-fg-muted text-sm">Drop Destination</label>
                                <input wire:model.defer="drop_destination" type="text" placeholder="+15551234567"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gap-6 grid grid-cols-1 md:grid-cols-4 pb-10 border-surface border-b">
                    <div>
                        <h2 class="font-medium">After Hours</h2>
                        <p class="text-fg-muted text-sm">Operating hours schedule.</p>
                    </div>
                    <div class="space-y-5 md:col-span-3">
                        <div class="flex items-center gap-3">
                            <input wire:model.live="always_open" type="checkbox" id="always_open"
                                class="bg-surface border-surface-2 rounded focus:ring-blue-500 text-blue-500" />
                            <label for="always_open" class="text-fg-muted text-sm">Always open (24/7)</label>
                        </div>

                        @if (!$always_open)
                            <div>
                                <label class="text-fg-muted text-sm">Timezone</label>
                                <select wire:model.defer="timezone"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                    @foreach ($timezones as $tz)
                                        <option value="{{ $tz }}" @selected($tz === $timezone)>
                                            {{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                @foreach (['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'] as $key => $label)
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-2 w-28">
                                            <input wire:model.live="hours.{{ $key }}.enabled"
                                                type="checkbox"
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

                <div class="flex justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                            Save Changes
                        </button>
                    </div>
                    @if (!$confirmingDelete)
                        <button type="button" wire:click="confirmDelete"
                            class="bg-red-600/10 hover:bg-red-600/20 px-4 py-2 rounded-md font-medium text-red-400 text-sm transition">
                            Delete In-Group
                        </button>
                    @else
                        <div class="flex items-center gap-3">
                            <span class="text-fg-muted text-sm">Are you sure?</span>
                            <button type="button" wire:click="delete"
                                class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                                Yes, Delete
                            </button>
                            <button type="button" wire:click="$set('confirmingDelete', false)"
                                class="bg-surface-2 px-4 py-2 rounded-md font-medium text-fg-muted text-sm transition">
                                Cancel
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    @endif

    {{-- Agents Tab --}}
    @if ($activeTab === 'agents')
        <div class="space-y-6 max-w-4xl">

            {{-- Add agent --}}
            <div class="space-y-4 bg-surface p-5 border border-surface rounded-xl">
                <h3 class="font-medium text-fg text-sm">Add Agent</h3>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="text-fg-muted text-xs">User</label>
                        <select wire:model.defer="addUserId"
                            class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                            <option value="">Select user…</option>
                            @foreach ($availableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="text-fg-muted text-xs">Priority</label>
                        <input wire:model.defer="addPriority" type="number" min="1" max="99"
                            class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                    </div>
                    <button wire:click="addAgent" type="button"
                        class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                        Add
                    </button>
                </div>
                @error('addUserId')
                    <p class="text-xs text-accent-red">{{ $message }}</p>
                @enderror
            </div>

            {{-- Assigned agents --}}
            <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
                <div class="px-5 py-4 border-surface border-b">
                    <h3 class="font-bold text-fg text-base">Assigned Agents ({{ $assignedAgents->count() }})</h3>
                </div>
                <table class="min-w-full text-fg text-sm">
                    <thead>
                        <tr
                            class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Priority</th>
                            <th class="px-5 py-3 text-left">Last Routed</th>
                            <th class="px-5 py-3 text-left">Active</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignedAgents as $agent)
                            <tr class="hover:bg-hover border-surface border-b transition">
                                <td class="px-5 py-3 font-medium">{{ $agent->first_name }} {{ $agent->last_name }}
                                </td>
                                <td class="px-5 py-3 text-fg-muted">{{ $agent->pivot->priority }}</td>
                                <td class="px-5 py-3 text-fg-muted text-xs">
                                    {{ $agent->pivot->last_call_at ? \Carbon\Carbon::parse($agent->pivot->last_call_at)->diffForHumans() : '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <input type="checkbox" @checked($agent->pivot->is_active)
                                        wire:click="toggleAgent({{ $agent->id }}, {{ $agent->pivot->is_active ? 'false' : 'true' }})"
                                        class="bg-surface border-surface-2 rounded focus:ring-blue-500 text-blue-500" />
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button wire:click="removeAgent({{ $agent->id }})" type="button"
                                        class="text-red-400 hover:text-red-300 text-xs transition">Remove</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-zinc-500 text-sm text-center italic">No
                                    agents assigned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- DIDs Tab --}}
    @if ($activeTab === 'dids')
        <div class="max-w-4xl">
            <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
                <div class="flex justify-between items-center px-5 py-4 border-surface border-b">
                    <h3 class="font-bold text-fg text-base">Linked DIDs ({{ $dids->count() }})</h3>
                    <a href="{{ route('did.create') }}" wire:navigate
                        class="text-blue-400 hover:text-blue-300 text-sm transition">+ Add DID</a>
                </div>
                <table class="min-w-full text-fg text-sm">
                    <thead>
                        <tr
                            class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                            <th class="px-5 py-3 text-left">Phone Number</th>
                            <th class="px-5 py-3 text-left">Description</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dids as $did)
                            <tr class="hover:bg-hover border-surface border-b transition">
                                <td class="px-5 py-3 font-mono">{{ $did->phone_number }}</td>
                                <td class="px-5 py-3 text-fg-muted">{{ $did->description ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @if ($did->is_active)
                                        <span class="text-green-400 text-xs">Active</span>
                                    @else
                                        <span class="text-fg-muted text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('did.edit', $did) }}" wire:navigate
                                        class="text-fg-muted hover:text-fg text-xs transition">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-zinc-500 text-sm text-center italic">No DIDs
                                    linked to this in-group.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
