@php
    $steps = [
        ['n' => 1, 'label' => 'Details'],
        ['n' => 2, 'label' => 'Dialer & Routing'],
        ['n' => 3, 'label' => 'Lead Form'],
        ['n' => 4, 'label' => 'Voice & Recording'],
        ['n' => 5, 'label' => 'Modules'],
    ];
@endphp

<div>
    @if ($open)
        <div class="fixed inset-0 z-50 flex flex-col bg-base" x-data @keydown.escape.window="$wire.close()">

            {{-- Top bar --}}
            <div class="flex items-center justify-between h-14 px-5 bg-surface border-b border-surface shrink-0">
                <div class="flex items-center gap-2">
                    <button wire:click="close" type="button"
                        class="p-1.5 rounded-lg text-fg-muted hover:text-fg hover:bg-hover transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </button>
                    <span class="text-xs text-fg-muted">Campaigns</span>
                    <span class="text-fg-muted">/</span>
                    <span class="text-sm font-semibold text-fg">{{ $mode === 'create' ? 'New Campaign' : $name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="save" wire:loading.attr="disabled" type="button"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition">
                        <span wire:loading.remove wire:target="save">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-0.5" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            {{ $mode === 'create' ? 'Create Campaign' : 'Save Changes' }}
                        </span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    <button wire:click="close" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-400 hover:text-white hover:bg-red-500 border border-red-500/30 hover:border-red-500 text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Cancel
                    </button>
                </div>
            </div>

            {{-- Sidebar + content --}}
            <div class="flex flex-1 min-h-0">

                {{-- Sidebar --}}
                <aside class="w-48 shrink-0 border-r border-surface bg-surface flex flex-col py-4 px-3 gap-0.5">
                    @foreach ($steps as $s)
                        <button type="button" wire:click="goToStep({{ $s['n'] }})" @class([
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition w-full group',
                            'bg-indigo-600/15 text-indigo-400' => $step === $s['n'],
                            'text-fg-muted hover:text-fg hover:bg-hover' => $step !== $s['n'],
                        ])>
                            <span @class([
                                'inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold shrink-0 transition',
                                'bg-indigo-600 text-white' => $step === $s['n'],
                                'bg-surface-2 text-fg-muted group-hover:bg-hover' => $step !== $s['n'],
                            ])>{{ $s['n'] }}</span>
                            <p class="text-xs font-semibold truncate">{{ $s['label'] }}</p>
                        </button>
                    @endforeach
                </aside>

                {{-- Scrollable content --}}
                <div class="flex-1 overflow-y-auto">
                    <div class="max-w-3xl mx-auto px-8 py-6 space-y-5">

                        @if ($errors->any())
                            <div
                                class="flex items-start gap-2 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 text-red-400 text-xs">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                Fix the errors below before saving.
                            </div>
                        @endif

                        {{-- ════════════════════════════════ STEP 1: DETAILS ════════════════════════════════ --}}
                        @if ($step === 1)
                            <div class="space-y-5">
                                <div>
                                    <h2 class="font-bold text-fg">Campaign Details</h2>
                                    <p class="text-xs text-fg-muted mt-0.5">Core information and campaign type.</p>
                                </div>

                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-fg-muted">Name <span
                                                class="text-red-400">*</span></label>
                                        <input wire:model="name" type="text" placeholder="e.g. Q3 Outbound Sales"
                                            @class([
                                                'w-full bg-surface-2 border rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition',
                                                'border-red-500' => $errors->has('name'),
                                                'border-surface' => !$errors->has('name'),
                                            ])>
                                        @error('name')
                                            <p class="text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-fg-muted">Description</label>
                                        <textarea wire:model="description" rows="2" placeholder="Optional description…"
                                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition resize-none"></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-fg-muted">Campaign Type</label>
                                        <div class="grid grid-cols-3 gap-3">
                                            @foreach (\App\Models\Campaign::TYPES as $t)
                                                @php $tc=["OUTBOUND"=>["ring"=>"ring-blue-500","bg"=>"bg-blue-500/10","text"=>"text-blue-400","dot"=>"bg-blue-400"],"INBOUND"=>["ring"=>"ring-emerald-500","bg"=>"bg-emerald-500/10","text"=>"text-emerald-400","dot"=>"bg-emerald-400"],"BLENDED"=>["ring"=>"ring-fuchsia-500","bg"=>"bg-fuchsia-500/10","text"=>"text-fuchsia-400","dot"=>"bg-fuchsia-400"]][$t]; @endphp
                                                <button type="button" wire:click="$set('type','{{ $t }}')"
                                                    @class([
                                                        'flex flex-col gap-1 p-3 rounded-xl transition text-left border-2',
                                                        $tc['ring'] . ' ' . $tc['bg'] => $type === $t,
                                                        'border-surface bg-surface-2 hover:border-zinc-600' => $type !== $t,
                                                    ])>
                                                    <span @class([
                                                        'text-xs font-bold flex items-center gap-1.5',
                                                        $tc['text'] => $type === $t,
                                                        'text-fg-muted' => $type !== $t,
                                                    ])>
                                                        <span
                                                            class="w-1.5 h-1.5 rounded-full {{ $tc['dot'] }} inline-block"></span>{{ $t }}
                                                    </span>
                                                    <span
                                                        class="text-[11px] text-fg-muted">{{ $t === 'OUTBOUND' ? 'Agents dial out' : ($t === 'INBOUND' ? 'Receive calls' : 'Both directions') }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                            <div
                                                class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                            </div>
                                        </label>
                                        <span class="text-sm text-fg">Active on creation</span>
                                    </div>
                                </div>

                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="text-xs font-semibold text-fg">Agent Script</label>
                                            <p class="text-[11px] text-fg-muted mt-0.5">Shown to agents during active
                                                calls.</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input wire:model.live="script_enabled" type="checkbox"
                                                class="sr-only peer">
                                            <div
                                                class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                            </div>
                                        </label>
                                    </div>
                                    @if ($script_enabled)
                                        <div wire:ignore x-data="{
                                            editor: null,
                                            init() {
                                                this.$nextTick(() => this.boot());
                                            },
                                            boot() {
                                                if (this.editor) {
                                                    this.editor.destroy().catch(() => {});
                                                    this.editor = null;
                                                }
                                                ClassicEditor.create(this.$refs.ckEl, {
                                                    toolbar: {
                                                        items: ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|',
                                                            'bulletedList', 'numberedList', '|', 'link', 'blockQuote', '|',
                                                            'undo', 'redo'
                                                        ],
                                                    },
                                                    heading: {
                                                        options: [
                                                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                                                            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                                                        ]
                                                    },
                                                }).then(ed => {
                                                    this.editor = ed;
                                                    ed.setData(@js($script ?? ''));
                                                    ed.model.document.on('change:data', () => {
                                                        $wire.set('script', ed.getData(), false);
                                                    });
                                                }).catch(err => console.error(err));
                                            },
                                            destroy() {
                                                if (this.editor) {
                                                    this.editor.destroy().catch(() => {});
                                                    this.editor = null;
                                                }
                                            }
                                        }" x-init="init()" @keydown.stop>
                                            <div x-ref="ckEl"></div>
                                        </div>
                                    @else
                                        <p class="text-xs text-fg-muted italic py-2">Script is disabled. Enable the
                                            toggle to write one.</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- ════════════════════════════════ STEP 2: DIALER & ROUTING ════════════════════════════════ --}}
                        @if ($step === 2)
                            <div class="space-y-5">
                                <div>
                                    <h2 class="font-bold text-fg">Dialer & Routing</h2>
                                    <p class="text-xs text-fg-muted mt-0.5">Call handling, inbound groups, and caller
                                        ID settings.</p>
                                </div>

                                {{-- Dialer --}}
                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                                    <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Dialer
                                        Settings</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Dial Mode <span
                                                    class="text-red-400">*</span></label>
                                            <select wire:model="dial_mode"
                                                class="w-full bg-surface-2 border @error('dial_mode') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                                @foreach (\App\Models\Campaign::DIAL_MODES as $m)
                                                    <option value="{{ $m }}">{{ ucfirst(strtolower($m)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('dial_mode')
                                                <p class="text-xs text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Dial Level <span
                                                    class="text-[11px] font-normal text-fg-muted">(predictive
                                                    ratio)</span></label>
                                            <input wire:model="dial_level" type="number" step="0.1"
                                                min="0.1" max="10"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Hopper Level</label>
                                            <input wire:model="hopper_level" type="number" min="1"
                                                max="1000"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                            <p class="text-[11px] text-fg-muted">Leads to pre-queue</p>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">ACW Timer <span
                                                    class="text-[11px] font-normal">(sec)</span></label>
                                            <input wire:model="acw_seconds" type="number" min="0"
                                                max="3600"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                            <p class="text-[11px] text-fg-muted">0 = disabled</p>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Max Simultaneous
                                                Calls</label>
                                            <input wire:model="max_calls" type="number" min="1"
                                                max="999" placeholder="Unlimited"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                                        </div>
                                    </div>
                                </div>

                                {{-- Caller ID & CID --}}
                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                                    <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Caller ID
                                    </h3>
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-fg-muted">Fixed Outbound Caller
                                            ID</label>
                                        <input wire:model="caller_id" type="text"
                                            placeholder="+1xxxxxxxxxx (leave blank to use CID group or Twilio default)"
                                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted font-mono focus:outline-none focus:border-zinc-500 transition">
                                        <p class="text-[11px] text-fg-muted">Used when CID rotation is off, or as
                                            fallback.</p>
                                    </div>
                                    <div class="flex items-center justify-between py-1">
                                        <div>
                                            <p class="text-sm font-medium text-fg">CID Rotation</p>
                                            <p class="text-[11px] text-fg-muted mt-0.5">Round-robin rotate numbers from
                                                a CID group.</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input wire:model.live="cid_rotation" type="checkbox"
                                                class="sr-only peer">
                                            <div
                                                class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                            </div>
                                        </label>
                                    </div>
                                    @if ($cid_rotation)
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">CID Group</label>
                                            @if ($allCidGroups->isEmpty())
                                                <p class="text-sm text-fg-muted">No CID groups available. Create one
                                                    first.</p>
                                            @else
                                                <select wire:model="cid_group_id"
                                                    class="w-full bg-surface-2 border @error('cid_group_id') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                                    <option value="">— None —</option>
                                                    @foreach ($allCidGroups as $cg)
                                                        <option value="{{ $cg->id }}">{{ $cg->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('cid_group_id')
                                                    <p class="text-xs text-red-400">{{ $message }}</p>
                                                @enderror
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Inbound Groups --}}
                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                                    <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Inbound
                                        Groups</h3>
                                    <p class="text-xs text-fg-muted">Select which inbound groups route calls to this
                                        campaign.</p>
                                    @if ($allInGroups->isEmpty())
                                        <p class="text-sm text-fg-muted">No inbound groups configured yet.</p>
                                    @else
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach ($allInGroups as $ig)
                                                @php $checked = in_array((string)$ig->id, array_map('strval', $selectedInGroupIds)); @endphp
                                                <label @class([
                                                    'flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition',
                                                    'border-indigo-500/60 bg-indigo-500/10' => $checked,
                                                    'border-surface bg-surface-2 hover:border-zinc-600' => !$checked,
                                                ])>
                                                    <input type="checkbox" wire:model="selectedInGroupIds"
                                                        value="{{ $ig->id }}"
                                                        class="mt-0.5 w-4 h-4 rounded border-surface text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 shrink-0">
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-medium text-fg truncate">
                                                            {{ $ig->name }}</p>
                                                        @if ($ig->description)
                                                            <p class="text-[11px] text-fg-muted truncate">
                                                                {{ $ig->description }}</p>
                                                        @endif
                                                        <span
                                                            class="inline-block mt-1 text-[10px] bg-surface border border-surface px-1.5 py-0.5 rounded text-fg-muted">{{ $ig->agent_routing ?? 'round_robin' }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- ════════════════════════════════ STEP 3: LEAD FORM ════════════════════════════════ --}}
                        @if ($step === 3)
                            @php
                                $fieldLibrary = [
                                    ['type' => 'text', 'label' => 'Text'],
                                    ['type' => 'textarea', 'label' => 'Long Text'],
                                    ['type' => 'email', 'label' => 'Email'],
                                    ['type' => 'phone', 'label' => 'Phone'],
                                    ['type' => 'number', 'label' => 'Number'],
                                    ['type' => 'date', 'label' => 'Date'],
                                    ['type' => 'select', 'label' => 'Dropdown'],
                                    ['type' => 'checkbox', 'label' => 'Checkbox'],
                                ];
                            @endphp
                            <div x-data="{
                                sel: { step: 0, field: null },
                                drag: { step: null, field: null },
                                palette: null,
                                clearDrag() {
                                    this.drag = { step: null, field: null };
                                    this.palette = null
                                },
                                dropField(s, f) {
                                    if (this.palette) {
                                        $wire.insertLeadFieldAt(s, f, this.palette);
                                        this.sel = { step: s, field: f };
                                        this.clearDrag();
                                        return
                                    }
                                    if (this.drag.field === null) return;
                                    if (this.drag.step === s) { if (this.drag.field !== f) $wire.moveLeadFieldTo(s, this.drag.field, f); } else $wire.moveLeadFieldAcrossSteps(this.drag.step, this.drag.field, s, f);
                                    this.sel = { step: s, field: f };
                                    this.clearDrag();
                                },
                                dropEnd(s, end) {
                                    if (this.palette) {
                                        $wire.insertLeadFieldAt(s, end, this.palette);
                                        this.sel = { step: s, field: Math.max(0, end) };
                                        this.clearDrag();
                                        return
                                    }
                                    if (this.drag.field === null) return;
                                    if (this.drag.step === s) $wire.moveLeadFieldTo(s, this.drag.field, end - 1);
                                    else $wire.moveLeadFieldAcrossSteps(this.drag.step, this.drag.field, s, end);
                                    this.sel = { step: s, field: Math.max(0, end - 1) };
                                    this.clearDrag();
                                }
                            }" class="space-y-4">
                                <div class="flex items-end justify-between gap-3">
                                    <div>
                                        <h2 class="font-bold text-fg">Lead Form Builder</h2>
                                        <p class="text-xs text-fg-muted mt-0.5">Design the data capture form. Skip if
                                            not needed.</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="space-y-0.5">
                                            <label class="text-[11px] text-fg-muted">Mode</label>
                                            <select wire:model.live="lead_process_mode"
                                                class="bg-surface-2 border border-surface rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:border-zinc-500 transition">
                                                <option value="single">Single Form</option>
                                                <option value="stepper">Multi-Step</option>
                                            </select>
                                        </div>
                                        @if ($lead_process_mode === 'stepper' || count($lead_process_steps) === 0)
                                            <button type="button" wire:click="addLeadStep"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                Add Step
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if (count($lead_process_steps) === 0)
                                    <div
                                        class="border-2 border-dashed border-surface rounded-xl p-10 text-center space-y-3">
                                        <p class="text-sm text-fg-muted">No form steps yet — add a step to start
                                            building.</p>
                                        <button type="button" wire:click="addLeadStep"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-sm">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Add First Step
                                        </button>
                                    </div>
                                @else
                                    <div class="grid grid-cols-[160px_minmax(0,1fr)_240px] gap-4 items-start">
                                        {{-- Palette --}}
                                        <div
                                            class="bg-surface border border-surface rounded-xl p-3 space-y-2 sticky top-0">
                                            <p class="text-xs font-semibold text-fg">Input Library</p>
                                            <p class="text-[11px] text-fg-muted">Drag or click to add</p>
                                            <div class="space-y-1">
                                                @foreach ($fieldLibrary as $lf)
                                                    <button type="button" draggable="true"
                                                        @dragstart="palette='{{ $lf['type'] }}'"
                                                        @dragend="clearDrag()"
                                                        @click="$wire.addLeadFieldOfType(sel.step,'{{ $lf['type'] }}')"
                                                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg border border-surface bg-surface-2 hover:bg-hover text-xs text-fg transition cursor-grab">
                                                        {{ $lf['label'] }}
                                                        <svg class="w-3 h-3 text-fg-muted" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.75"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                                        </svg>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Canvas --}}
                                        <div class="space-y-3">
                                            @foreach ($lead_process_steps as $si => $stepData)
                                                <div class="bg-surface border border-surface rounded-xl p-3 space-y-3"
                                                    wire:key="lp-step-{{ $si }}"
                                                    @click="sel.step={{ $si }}"
                                                    :class="sel.step === {{ $si }} ? 'border-cyan-500/50' : ''">
                                                    <div class="flex items-center gap-2">
                                                        <input type="text"
                                                            wire:model.blur="lead_process_steps.{{ $si }}.title"
                                                            placeholder="Step title"
                                                            class="flex-1 bg-surface-2 border border-surface rounded-lg px-2.5 py-1.5 text-sm text-fg focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                                        <button type="button"
                                                            wire:click="removeLeadStep({{ $si }})"
                                                            class="text-xs text-red-400 hover:text-red-300 px-2 py-1.5 rounded-lg hover:bg-red-500/10 transition">Remove</button>
                                                    </div>
                                                    <div class="space-y-2 min-h-[50px] border border-dashed border-surface rounded-lg p-2"
                                                        @dragover.prevent
                                                        @drop.prevent="dropEnd({{ $si }},{{ count($stepData['fields'] ?? []) }})"
                                                        :class="(drag.field !== null || palette) ?
                                                        'border-cyan-500/40 bg-cyan-500/5' : ''">
                                                        @foreach ($stepData['fields'] ?? [] as $fi => $field)
                                                            <div wire:key="lp-field-{{ $si }}-{{ $field['uid'] ?? $fi }}"
                                                                draggable="true"
                                                                @click.stop="sel={step:{{ $si }},field:{{ $fi }}}"
                                                                @dragstart="drag={step:{{ $si }},field:{{ $fi }}}"
                                                                @dragend="clearDrag()" @dragover.prevent
                                                                @drop.prevent="dropField({{ $si }},{{ $fi }})"
                                                                class="flex items-center justify-between px-2.5 py-2 rounded-lg border border-surface bg-surface/50 cursor-pointer text-xs"
                                                                :class="sel.step === {{ $si }} && sel.field ===
                                                                    {{ $fi }} ?
                                                                    'ring-1 ring-indigo-500/60 border-indigo-500/40' :
                                                                    ''">
                                                                <div class="flex items-center gap-2 min-w-0">
                                                                    <span
                                                                        class="text-[10px] text-fg-muted uppercase shrink-0">{{ $field['type'] ?? 'text' }}</span>
                                                                    <span
                                                                        class="text-fg truncate">{{ $field['label'] ?? '' ?: 'Untitled' }}{{ $field['required'] ?? false ? ' *' : '' }}</span>
                                                                </div>
                                                                <button type="button"
                                                                    wire:click.stop="removeLeadField({{ $si }},{{ $fi }})"
                                                                    class="text-[10px] text-red-400 hover:text-red-300 ml-2 shrink-0">✕</button>
                                                            </div>
                                                        @endforeach
                                                        @if (empty($stepData['fields']))
                                                            <p class="text-[11px] text-fg-muted text-center py-3">Drop
                                                                inputs here</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Properties --}}
                                        <div
                                            class="bg-surface border border-surface rounded-xl p-3 space-y-3 sticky top-0">
                                            <p class="text-xs font-semibold text-fg">Field Properties</p>
                                            @foreach ($lead_process_steps as $si => $s)
                                                @foreach ($s['fields'] ?? [] as $fi => $field)
                                                    <div x-show="sel.step==={{ $si }}&&sel.field==={{ $fi }}"
                                                        wire:key="lp-props-{{ $si }}-{{ $field['uid'] ?? $fi }}"
                                                        class="space-y-2.5">
                                                        <div><label class="text-[11px] text-fg-muted">Key</label>
                                                            <input type="text"
                                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.key"
                                                                placeholder="e.g. first_name"
                                                                class="w-full bg-surface-2 border border-surface rounded px-2 py-1 text-xs font-mono text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 mt-0.5">
                                                            @error("lead_process_steps.{$si}.fields.{$fi}.key")
                                                                <p class="text-[10px] text-red-400">{{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>
                                                        <div><label class="text-[11px] text-fg-muted">Label</label>
                                                            <input type="text"
                                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.label"
                                                                class="w-full bg-surface-2 border border-surface rounded px-2 py-1 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 mt-0.5">
                                                        </div>
                                                        <div><label
                                                                class="text-[11px] text-fg-muted">Placeholder</label>
                                                            <input type="text"
                                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.placeholder"
                                                                class="w-full bg-surface-2 border border-surface rounded px-2 py-1 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 mt-0.5">
                                                        </div>
                                                        @if (($field['type'] ?? '') === 'select')
                                                            <div><label class="text-[11px] text-fg-muted">Options (one
                                                                    per line)</label>
                                                                <textarea wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.options_text"
                                                                    rows="3"
                                                                    class="w-full bg-surface-2 border border-surface rounded px-2 py-1 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 mt-0.5 resize-none"></textarea>
                                                            </div>
                                                        @endif
                                                        <label class="flex items-center gap-2 cursor-pointer">
                                                            <input type="checkbox"
                                                                wire:model="lead_process_steps.{{ $si }}.fields.{{ $fi }}.required"
                                                                class="rounded border-surface text-indigo-600 w-3.5 h-3.5">
                                                            <span class="text-xs text-fg">Required</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                            <p x-show="sel.field===null" class="text-xs text-fg-muted">Click a field
                                                to edit properties.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- ════════════════════════════════ STEP 4: VOICE & RECORDING ════════════════════════════════ --}}
                        @if ($step === 4)
                            <div class="space-y-5">
                                <div>
                                    <h2 class="font-bold text-fg">Voice & Recording</h2>
                                    <p class="text-xs text-fg-muted mt-0.5">TTS messages, hold music, and call
                                        recording settings.</p>
                                </div>

                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                                    <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">TTS Voice
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Voice</label>
                                            <select wire:model="tts_voice"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                                @foreach (\App\Models\Campaign::TTS_VOICES as $v)
                                                    <option value="{{ $v }}">
                                                        {{ $v === 'alice' ? 'Alice (neural)' : ucfirst($v) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Language</label>
                                            <select wire:model="tts_language"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                                @foreach (['en-US' => 'English (US)', 'en-GB' => 'English (UK)', 'es-US' => 'Spanish (US)', 'es-ES' => 'Spanish (ES)', 'fr-FR' => 'French', 'de-DE' => 'German', 'pt-BR' => 'Portuguese (BR)'] as $code => $lbl)
                                                    <option value="{{ $code }}">{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Greeting
                                                Message</label>
                                            <input wire:model="greeting_message" type="text"
                                                placeholder="Welcome to Acme support…"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                                            <p class="text-[11px] text-fg-muted">Played when caller first connects.</p>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Hold Music
                                                URL</label>
                                            <input wire:model="hold_music_url" type="url" placeholder="https://…"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                                            <p class="text-[11px] text-fg-muted">Leave blank for Twilio default.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-3">
                                    <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">End-of-Call
                                        Messages</h3>
                                    @foreach (['tts_completed' => 'Completed', 'tts_busy' => 'Busy', 'tts_no_answer' => 'No Answer', 'tts_failed' => 'Failed', 'tts_canceled' => 'Canceled'] as $key => $lbl)
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="text-xs text-fg-muted w-20 pt-2 shrink-0">{{ $lbl }}</span>
                                            <div class="flex-1">
                                                <input wire:model="{{ $key }}" type="text"
                                                    class="w-full bg-surface-2 border @error($key) border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                                @error($key)
                                                    <p class="text-xs text-red-400 mt-0.5">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                                    <h3 class="text-xs font-semibold text-fg border-b border-surface pb-2">Call
                                        Recording</h3>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-fg">Enable Recording</p>
                                            <p class="text-[11px] text-fg-muted mt-0.5">Record all calls for this
                                                campaign.</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input wire:model.live="recording_enabled" type="checkbox"
                                                class="sr-only peer">
                                            <div
                                                class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                            </div>
                                        </label>
                                    </div>
                                    @if ($recording_enabled)
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-fg-muted">Record
                                                Channels</label>
                                            <select wire:model="recording_channels"
                                                class="w-56 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                                                @foreach (\App\Models\Campaign::REC_CHANNELS as $ch)
                                                    <option value="{{ $ch }}">{{ ucfirst($ch) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button wire:click="save" wire:loading.attr="disabled" type="button"
                                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
                                        <span wire:loading.remove wire:target="save">
                                            <svg class="w-4 h-4 inline -mt-0.5 mr-0.5" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            {{ $mode === 'create' ? 'Create Campaign' : 'Save Changes' }}
                                        </span>
                                        <span wire:loading wire:target="save">Saving…</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- ════════════════════════════════ STEP 5: MODULES ════════════════════════════════ --}}
                        @if ($step === 5)
                            <div class="space-y-5">
                                <div>
                                    <h2 class="font-bold text-fg">Campaign Modules</h2>
                                    <p class="text-xs text-fg-muted mt-0.5">Enable or disable features and pages for
                                        this campaign.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    @foreach (\App\Models\Campaign::MODULES as $key => $mod)
                                        <div @class([
                                            'flex items-start justify-between gap-4 p-4 rounded-xl border transition',
                                            'border-indigo-500/40 bg-indigo-500/5' => $modules[$key] ?? $mod['default'],
                                            'border-surface bg-surface' => !($modules[$key] ?? $mod['default']),
                                        ])>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-fg">{{ $mod['label'] }}</p>
                                                <p class="text-[11px] text-fg-muted mt-0.5 leading-relaxed">
                                                    {{ $mod['desc'] }}</p>
                                            </div>
                                            <label
                                                class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                                <input wire:model.live="modules.{{ $key }}" type="checkbox"
                                                    class="sr-only peer">
                                                <div
                                                    class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button wire:click="save" wire:loading.attr="disabled" type="button"
                                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
                                        <span wire:loading.remove wire:target="save">
                                            <svg class="w-4 h-4 inline -mt-0.5 mr-0.5" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            {{ $mode === 'create' ? 'Create Campaign' : 'Save Changes' }}
                                        </span>
                                        <span wire:loading wire:target="save">Saving…</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Step footer --}}
                        <div class="flex items-center justify-between pt-4 border-t border-surface">
                            <button @class([
                                'inline-flex items-center gap-2 text-sm px-4 py-2 rounded-lg border border-surface hover:bg-hover transition',
                                'invisible' => $step === 1,
                                'text-fg-muted hover:text-fg' => $step > 1,
                            ]) wire:click="prevStep" type="button">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                </svg>
                                Back
                            </button>
                            <span class="text-xs text-fg-muted">Step {{ $step }} of 5</span>
                            @if ($step < 5)
                                <button wire:click="nextStep" type="button"
                                    class="inline-flex items-center gap-2 text-sm text-fg-muted hover:text-fg px-4 py-2 rounded-lg border border-surface hover:bg-hover transition">
                                    Next <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            @else
                                <div class="w-[80px]"></div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
