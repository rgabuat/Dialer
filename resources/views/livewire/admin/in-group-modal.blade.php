<div>
@if ($open)
<div class="fixed inset-0 z-50 flex flex-col bg-base" x-data @keydown.escape.window="$wire.close()">

    {{-- Top bar --}}
    <div class="flex items-center justify-between h-14 px-5 bg-surface border-b border-surface shrink-0">
        <div class="flex items-center gap-2">
            <button wire:click="close" type="button" class="p-1.5 rounded-lg text-fg-muted hover:text-fg hover:bg-hover transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
            </button>
            <span class="text-xs text-fg-muted">Inbound Groups</span>
            <span class="text-fg-muted">/</span>
            <span class="text-sm font-semibold text-fg">{{ $mode === "create" ? "New Inbound Group" : $name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="save" wire:loading.attr="disabled" type="button"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition">
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4 inline -mt-0.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    {{ $mode === "create" ? "Create Group" : "Save Changes" }}
                </span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
            <button wire:click="close" type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-400 hover:text-white hover:bg-red-500 border border-red-500/30 hover:border-red-500 text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
                Cancel
            </button>
        </div>
    </div>

    {{-- Sidebar + content --}}
    <div class="flex flex-1 min-h-0">

        {{-- Sidebar stepper --}}
        <aside class="w-48 shrink-0 border-r border-surface bg-surface flex flex-col py-4 px-3 gap-0.5">
            @foreach ([['n'=>1,'label'=>'Settings'],['n'=>2,'label'=>'DIDs'],['n'=>3,'label'=>'Agents']] as $s)
                <button type="button" wire:click="$set('step', {{ $s['n'] }})"
                    @class(['flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition w-full group',
                        'bg-indigo-600/15 text-indigo-400' => $step === $s['n'],
                        'text-fg-muted hover:text-fg hover:bg-hover' => $step !== $s['n']])>
                    <span @class(['inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold shrink-0 transition',
                        'bg-indigo-600 text-white' => $step === $s['n'],
                        'bg-surface-2 text-fg-muted group-hover:bg-hover' => $step !== $s['n']])>{{ $s['n'] }}</span>
                    <p class="text-xs font-semibold">{{ $s['label'] }}</p>
                </button>
            @endforeach
        </aside>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-3xl mx-auto px-8 py-6 space-y-5">

                @if ($errors->any())
                    <div class="flex items-start gap-2 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 text-red-400 text-xs">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                        </svg>
                        Fix the errors below before saving.
                    </div>
                @endif

                {{-- ══════ STEP 1: SETTINGS ══════ --}}
                @if ($step === 1)
                @php $days = ["mon"=>"Monday","tue"=>"Tuesday","wed"=>"Wednesday","thu"=>"Thursday","fri"=>"Friday","sat"=>"Saturday","sun"=>"Sunday"]; @endphp
                <div class="space-y-5">
                    <div><h2 class="font-bold text-fg">Group Settings</h2><p class="text-xs text-fg-muted mt-0.5">Details, routing, queue, hours and campaign assignment.</p></div>

                    {{-- Details --}}
                    <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                        <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Details</h3>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-fg-muted">Name <span class="text-red-400">*</span></label>
                            <input wire:model="name" type="text" placeholder="e.g. Sales Inbound"
                                @class(["w-full bg-surface-2 border rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition",
                                    "border-red-500"=>$errors->has("name"),"border-surface"=>!$errors->has("name")])>
                            @error("name")<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-fg-muted">Description</label>
                            <textarea wire:model="description" rows="2" placeholder="Optional description..." class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted">Agent Routing</label>
                                <select wire:model="agent_routing" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                    <option value="ring_all">Ring All</option>
                                    <option value="round_robin">Round Robin</option>
                                    <option value="fewest_calls">Fewest Calls</option>
                                    <option value="longest_idle">Longest Idle</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted">Queue Priority <span class="text-[11px] font-normal text-fg-muted">(1=highest)</span></label>
                                <input wire:model="queue_priority" type="number" min="1" max="999" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                <div class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                            <span class="text-sm text-fg">Active</span>
                        </div>
                    </div>

                    {{-- Queue & Drop --}}
                    <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                        <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Queue & Drop</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted">Max Ring Wait (sec)</label>
                                <input wire:model="max_wait_seconds" type="number" min="1" max="3600" placeholder="No limit" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted">Queue Max Wait (sec)</label>
                                <input wire:model="queue_max_wait_seconds" type="number" min="0" max="86400" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted">Drop Action</label>
                                <select wire:model.live="drop_action" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                    <option value="hangup">Hang Up</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="voicemail">Voicemail</option>
                                </select>
                            </div>
                            @if ($drop_action !== "hangup")
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-fg-muted">{{ $drop_action === "transfer" ? "Transfer To" : "Voicemail Box" }}</label>
                                    <input wire:model="drop_destination" type="text" placeholder="+1xxxxxxxxxx" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted font-mono focus:outline-none focus:border-zinc-500 transition">
                                </div>
                            @endif
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-fg-muted">Screen Pop URL</label>
                            <input wire:model="web_form_url" type="url" placeholder="https://crm.example.com/contact?phone={phone}" class="w-full bg-surface-2 border @error('web_form_url') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                            @error("web_form_url")<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Business Hours --}}
                    <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                        <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Business Hours</h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-fg">Always Open</p>
                                <p class="text-[11px] text-fg-muted mt-0.5">Accept calls 24/7</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model.live="always_open" type="checkbox" class="sr-only peer">
                                <div class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                        </div>
                        @if (!$always_open)
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted">Timezone</label>
                                <select wire:model="timezone" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                    @foreach (\DateTimeZone::listIdentifiers(\DateTimeZone::ALL) as $tz)
                                        <option value="{{ $tz }}" {{ $timezone === $tz ? "selected" : "" }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                @foreach ($days as $key => $label)
                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-2 w-28 cursor-pointer">
                                            <input type="checkbox" wire:model="hours.{{ $key }}.enabled" class="rounded border-surface text-indigo-600 w-4 h-4">
                                            <span class="text-sm text-fg">{{ $label }}</span>
                                        </label>
                                        @if ($hours[$key]["enabled"])
                                            <input type="time" wire:model="hours.{{ $key }}.open" class="bg-surface-2 border border-surface rounded-lg px-2.5 py-1.5 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                            <span class="text-xs text-fg-muted">to</span>
                                            <input type="time" wire:model="hours.{{ $key }}.close" class="bg-surface-2 border border-surface rounded-lg px-2.5 py-1.5 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                        @else
                                            <span class="text-xs text-fg-muted italic">Closed</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-surface">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-fg-muted">After-Hours Action</label>
                                    <select wire:model.live="after_hours_action" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                        <option value="hangup">Hang Up</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="voicemail">Voicemail</option>
                                    </select>
                                </div>
                                @if ($after_hours_action !== "hangup")
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-fg-muted">{{ $after_hours_action === "transfer" ? "Transfer To" : "Voicemail Box" }}</label>
                                        <input wire:model="after_hours_destination" type="text" placeholder="+1xxxxxxxxxx" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted font-mono focus:outline-none focus:border-zinc-500 transition">
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Campaign --}}
                    <div class="bg-surface border border-surface rounded-xl p-5 space-y-3">
                        <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Campaign Assignment</h3>
                        <select wire:model="campaign_id" class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            <option value="">— None —</option>
                            @foreach ($allCampaigns as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="nextStep" type="button"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                            Next: DIDs
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endif

                {{-- ══════ STEP 2: DIDS ══════ --}}
                @if ($step === 2)
                <div class="space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-fg">DIDs — Inbound Numbers</h2>
                            <p class="text-xs text-fg-muted mt-0.5">Assign Twilio numbers that route inbound calls to this group.</p>
                        </div>
                        <button wire:click="prevStep" type="button" class="text-sm text-fg-muted hover:text-fg flex items-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Back
                        </button>
                    </div>

                    @if ($mode === "create")
                        <div class="bg-surface border border-surface rounded-xl p-10 text-center space-y-2">
                            <p class="text-sm text-fg-muted">Create the group first, then come back to assign DIDs.</p>
                        </div>
                    @else
                        <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                            {{-- Assigned --}}
                            @if ($dids->isEmpty())
                                <p class="text-sm text-fg-muted">No numbers assigned yet.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach ($dids as $did)
                                        <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-surface bg-surface-2">
                                            <div>
                                                <p class="text-sm font-semibold text-fg font-mono">{{ $did->phone_number }}</p>
                                                @if ($did->cidNumber)
                                                    <p class="text-[11px] text-fg-muted mt-0.5">{{ $did->cidNumber->friendly_name ?: $did->cidNumber->phone_number }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click="toggleDid({{ $did->id }})"
                                                    @class(["px-2.5 py-1 rounded-lg text-xs font-medium transition",
                                                        "bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20"=>$did->is_active,
                                                        "bg-surface text-fg-muted hover:bg-hover"=>!$did->is_active])>
                                                    {{ $did->is_active ? "Active" : "Inactive" }}
                                                </button>
                                                <button type="button" wire:click="removeDid({{ $did->id }})"
                                                    class="p-1.5 rounded-lg text-fg-muted hover:text-red-400 hover:bg-red-500/10 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Add --}}
                            @if ($availableCids->isNotEmpty())
                                <div class="flex items-center gap-2 pt-3 border-t border-surface">
                                    <select wire:model="addCidNumberId" class="flex-1 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                        <option value="">— Select CID number —</option>
                                        @foreach ($availableCids as $cid)
                                            <option value="{{ $cid->id }}">{{ $cid->phone_number }}{{ $cid->friendly_name ? " — {$cid->friendly_name}" : "" }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="addDid"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-indigo-500/40 text-indigo-400 hover:bg-indigo-500/10 text-xs font-medium transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        Assign
                                    </button>
                                </div>
                                @error("addCidNumberId")<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                            @else
                                <p class="text-xs text-fg-muted italic pt-3 border-t border-surface">All CID numbers are already assigned to groups.</p>
                            @endif
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <button wire:click="nextStep" type="button"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                            Next: Agents
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endif

                {{-- ══════ STEP 3: AGENTS ══════ --}}
                @if ($step === 3)
                <div class="space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-fg">Agents</h2>
                            <p class="text-xs text-fg-muted mt-0.5">Assign agents to this inbound group with their priority.</p>
                        </div>
                        <button wire:click="prevStep" type="button" class="text-sm text-fg-muted hover:text-fg flex items-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Back
                        </button>
                    </div>

                    @if ($mode === "create")
                        <div class="bg-surface border border-surface rounded-xl p-10 text-center space-y-2">
                            <p class="text-sm text-fg-muted">Create the group first, then come back to assign agents.</p>
                        </div>
                    @else
                        <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                            {{-- Assigned agents --}}
                            @if ($assignedAgents->isEmpty())
                                <p class="text-sm text-fg-muted">No agents assigned yet.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach ($assignedAgents as $agent)
                                        <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-surface bg-surface-2">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 text-xs font-bold shrink-0">
                                                    {{ strtoupper(substr($agent->first_name ?? "?", 0, 1)) }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-fg truncate">{{ $agent->first_name }} {{ $agent->last_name }}</p>
                                                    <p class="text-[11px] text-fg-muted">Priority {{ $agent->pivot->priority }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click="toggleAgent({{ $agent->id }}, {{ $agent->pivot->is_active ? 'false' : 'true' }})"
                                                    @class(["px-2.5 py-1 rounded-lg text-xs font-medium transition",
                                                        "bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20"=>$agent->pivot->is_active,
                                                        "bg-surface text-fg-muted hover:bg-hover"=>!$agent->pivot->is_active])>
                                                    {{ $agent->pivot->is_active ? "Active" : "Paused" }}
                                                </button>
                                                <button type="button" wire:click="removeAgent({{ $agent->id }})"
                                                    class="p-1.5 rounded-lg text-fg-muted hover:text-red-400 hover:bg-red-500/10 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Add agent --}}
                            @if ($availableUsers->isNotEmpty())
                                <div class="flex items-center gap-2 pt-3 border-t border-surface">
                                    <select wire:model="addUserId" class="flex-1 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                        <option value="">— Select agent —</option>
                                        @foreach ($availableUsers as $u)
                                            <option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <label class="text-xs text-fg-muted">Priority</label>
                                        <input wire:model="addPriority" type="number" min="1" max="999"
                                            class="w-16 bg-surface-2 border border-surface rounded-lg px-2 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                    </div>
                                    <button type="button" wire:click="addAgent"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-indigo-500/40 text-indigo-400 hover:bg-indigo-500/10 text-xs font-medium transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        Add
                                    </button>
                                </div>
                                @error("addUserId")<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                            @else
                                <p class="text-xs text-fg-muted italic pt-3 border-t border-surface">All agents are already assigned to this group.</p>
                            @endif
                        </div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endif
</div>