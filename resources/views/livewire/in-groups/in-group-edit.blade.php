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
            <div class="space-y-10">

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
                                <label class="text-fg-muted text-sm">Ring Timeout (seconds)</label>
                                <input wire:model.defer="max_wait_seconds" type="number" min="1" max="3600"
                                    placeholder="20"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                                <p class="mt-1 text-xs text-fg-muted">How long to ring agents per attempt.</p>
                            </div>
                            <div>
                                <label class="text-fg-muted text-sm">Queue Max Wait (seconds)</label>
                                <input wire:model.defer="queue_max_wait_seconds" type="number" min="0"
                                    max="86400" placeholder="300"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                                <p class="mt-1 text-xs text-fg-muted">Total hold time before drop action. 0 = skip
                                    queue.</p>
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
                                <select wire:model.live="drop_action"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                    <option value="hangup">Hang Up</option>
                                    <option value="transfer">Transfer to Number</option>
                                    <option value="voicemail">Voicemail</option>
                                </select>
                                <p class="mt-1 text-xs text-fg-muted">
                                    @if ($drop_action === 'hangup')
                                        Caller will be disconnected when max wait is exceeded.
                                    @elseif ($drop_action === 'transfer')
                                        Call will be forwarded to the number below.
                                    @elseif ($drop_action === 'voicemail')
                                        Caller will be sent to voicemail.
                                    @endif
                                </p>
                            </div>
                            @if ($drop_action === 'transfer')
                                <div>
                                    <label class="text-fg-muted text-sm">Drop Destination</label>
                                    <input wire:model.defer="drop_destination" type="text" placeholder="+15551234567"
                                        class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                                </div>
                            @endif
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
                                    <select wire:model.live="after_hours_action"
                                        class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                        <option value="hangup">Hang Up</option>
                                        <option value="transfer">Transfer to Number</option>
                                        <option value="voicemail">Voicemail</option>
                                    </select>
                                </div>
                                @if ($after_hours_action === 'transfer')
                                    <div>
                                        <label class="text-fg-muted text-sm">After-Hours Destination</label>
                                        <input wire:model.defer="after_hours_destination" type="text"
                                            placeholder="+15551234567"
                                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                                    </div>
                                @endif
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
        <div class="space-y-6">

            {{-- Add agent form --}}
            <div class="bg-surface border border-surface rounded-xl p-5">
                <h3 class="font-semibold text-fg text-sm mb-4">Add Agent to Queue</h3>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="text-fg-muted text-xs block mb-1">Agent</label>
                        <select wire:model.defer="addUserId"
                            class="bg-surface-2 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                            <option value="">Select agent…</option>
                            @foreach ($availableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }} —
                                    {{ $u->email }}</option>
                            @endforeach
                        </select>
                        @error('addUserId')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="w-32">
                        <label class="text-fg-muted text-xs block mb-1">Priority <span class="text-zinc-600">(1 =
                                highest)</span></label>
                        <input wire:model.defer="addPriority" type="number" min="1" max="99"
                            class="bg-surface-2 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                    </div>
                    <button wire:click="addAgent" type="button"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition shrink-0">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Add Agent
                    </button>
                </div>
            </div>

            {{-- Assigned agents table --}}
            <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
                <div class="flex items-center justify-between px-5 py-4 border-surface border-b">
                    <h3 class="font-bold text-fg text-base">Assigned Agents</h3>
                    <span class="bg-surface-2 px-2.5 py-0.5 rounded-full text-fg-muted text-xs font-medium">
                        {{ $assignedAgents->count() }}
                    </span>
                </div>
                <table class="min-w-full text-fg text-sm">
                    <thead>
                        <tr
                            class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                            <th class="px-5 py-3 w-full text-left">Agent</th>
                            <th class="px-5 py-3 text-left whitespace-nowrap">Priority</th>
                            <th class="px-5 py-3 text-left whitespace-nowrap">Last Routed</th>
                            <th class="px-5 py-3 text-left whitespace-nowrap">Active in Queue</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignedAgents as $agent)
                            <tr class="hover:bg-hover border-surface border-b transition">
                                {{-- Agent with initials avatar --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($agent->first_name, 0, 1) . substr($agent->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-fg leading-tight">{{ $agent->first_name }}
                                                {{ $agent->last_name }}</div>
                                            <div class="text-fg-muted text-xs">{{ $agent->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- Priority badge --}}
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center justify-center bg-surface-2 rounded-md w-8 h-7 text-fg text-sm font-mono font-semibold">
                                        {{ $agent->pivot->priority }}
                                    </span>
                                </td>
                                {{-- Last routed --}}
                                <td class="px-5 py-3 text-fg-muted text-xs whitespace-nowrap">
                                    {{ $agent->pivot->last_call_at ? \Carbon\Carbon::parse($agent->pivot->last_call_at)->diffForHumans() : '—' }}
                                </td>
                                {{-- Toggle switch --}}
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <button type="button"
                                        wire:click="toggleAgent({{ $agent->id }}, {{ $agent->pivot->is_active ? 'false' : 'true' }})"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none {{ $agent->pivot->is_active ? 'bg-blue-600' : 'bg-surface-3' }}"
                                        title="{{ $agent->pivot->is_active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}">
                                        <span
                                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition duration-200 {{ $agent->pivot->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                    </button>
                                </td>
                                {{-- Remove --}}
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <button wire:click="removeAgent({{ $agent->id }})"
                                        wire:confirm="Remove {{ $agent->first_name }} {{ $agent->last_name }} from this in-group?"
                                        type="button"
                                        class="inline-flex items-center gap-1 text-fg-muted hover:text-red-400 text-xs transition">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-heroicon-o-user-group class="w-8 h-8 text-zinc-700" />
                                        <p class="text-zinc-500 text-sm">No agents assigned yet.</p>
                                        <p class="text-zinc-600 text-xs">Use the form above to add agents to this
                                            queue.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- DIDs Tab --}}
    @if ($activeTab === 'dids')
        <div>
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
                            <th class="px-5 py-3 w-full text-left">Phone Number</th>
                            <th class="px-5 py-3 text-left whitespace-nowrap">Friendly Name</th>
                            <th class="px-5 py-3 text-left whitespace-nowrap">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dids as $did)
                            <tr class="hover:bg-hover border-surface border-b transition">
                                <td class="px-5 py-3 font-mono font-semibold text-fg">{{ $did->phone_number }}</td>
                                <td class="px-5 py-3 text-fg-muted text-sm">
                                    {{ $did->cidNumber?->friendly_name ?? '—' }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if ($did->is_active)
                                        <span
                                            class="inline-flex items-center gap-1.5 bg-green-500/10 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide text-accent-green">
                                            <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Active
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 rounded-md font-bold text-fg-muted text-xs uppercase tracking-wide">
                                            <span class="bg-surface-3 rounded-full w-1.5 h-1.5"></span>Inactive
                                        </span>
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
