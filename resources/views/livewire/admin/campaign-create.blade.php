<div class="max-w-5xl space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-fg-muted">
        <a href="{{ route('admin.campaigns.index') }}" class="hover:text-fg transition">Campaigns</a>
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span class="text-fg">New Campaign</span>
    </div>

    {{-- ── Stepper indicator ── --}}
    @php
        $steps = [
            ['n' => 1, 'label' => 'Details', 'desc' => 'Name, type & script'],
            ['n' => 2, 'label' => 'Lead Form', 'desc' => 'Data capture fields'],
            ['n' => 3, 'label' => 'Call Settings', 'desc' => 'Dialer, voice & recording'],
        ];
    @endphp
    <div class="bg-surface border border-surface rounded-xl px-6 py-4">
        <ol class="flex items-center gap-0">
            @foreach ($steps as $i => $s)
                <li class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                    <div class="flex items-center gap-3 shrink-0">
                        <span @class([
                            'inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold transition',
                            'bg-indigo-600 text-white' => $step == $s['n'],
                            'bg-indigo-600/80 text-white' => $step > $s['n'],
                            'bg-surface-2 text-fg-muted border border-surface' => $step < $s['n'],
                        ])>
                            @if ($step > $s['n'])
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @else
                                {{ $s['n'] }}
                            @endif
                        </span>
                        <div class="leading-tight">
                            <p @class([
                                'text-xs font-semibold',
                                'text-fg' => $step >= $s['n'],
                                'text-fg-muted' => $step < $s['n'],
                            ])>{{ $s['label'] }}</p>
                            <p class="text-[11px] text-fg-muted hidden sm:block">{{ $s['desc'] }}</p>
                        </div>
                    </div>
                    @if ($i < count($steps) - 1)
                        <div class="flex-1 h-px bg-surface mx-4"></div>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 1 — Details
    ════════════════════════════════════════════════════════════════════════ --}}
    @if ($step === 1)
        <div class="space-y-5">
            <div class="bg-surface border border-surface rounded-xl p-6 space-y-5">
                <div>
                    <h2 class="font-semibold text-fg">Campaign Details</h2>
                    <p class="text-xs text-fg-muted mt-0.5">Basic information and campaign type.</p>
                </div>

                {{-- Name --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-fg-muted">Name <span
                            class="text-red-400">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. Q3 Outbound Sales"
                        class="w-full bg-surface-2 border @error('name') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                    @error('name')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-fg-muted">Description</label>
                    <textarea wire:model="description" rows="3" placeholder="Optional description…"
                        class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition resize-none"></textarea>
                </div>

                {{-- Type --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-fg-muted">Campaign Type <span
                            class="text-red-400">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach (\App\Models\Campaign::TYPES as $t)
                            @php
                                $colors = [
                                    'OUTBOUND' => [
                                        'ring' => 'ring-blue-500',
                                        'bg' => 'bg-blue-500/10',
                                        'text' => 'text-blue-400',
                                        'dot' => 'bg-blue-400',
                                    ],
                                    'INBOUND' => [
                                        'ring' => 'ring-emerald-500',
                                        'bg' => 'bg-emerald-500/10',
                                        'text' => 'text-emerald-400',
                                        'dot' => 'bg-emerald-400',
                                    ],
                                    'BLENDED' => [
                                        'ring' => 'ring-fuchsia-500',
                                        'bg' => 'bg-fuchsia-500/10',
                                        'text' => 'text-fuchsia-400',
                                        'dot' => 'bg-fuchsia-400',
                                    ],
                                ];
                                $c = $colors[$t];
                            @endphp
                            <button type="button" wire:click="$set('type', '{{ $t }}')"
                                @class([
                                    'relative flex flex-col items-start gap-1 p-4 rounded-xl border-2 transition text-left',
                                    $c['ring'] . ' ' . $c['bg'] => $type === $t,
                                    'border-surface bg-surface-2 hover:border-zinc-600' => $type !== $t,
                                ])>
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-bold {{ $type === $t ? $c['text'] : 'text-fg-muted' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }} inline-block"></span>
                                    {{ $t }}
                                </span>
                                <span class="text-[11px] text-fg-muted">
                                    @if ($t === 'OUTBOUND')
                                        Agents dial out to leads
                                    @elseif ($t === 'INBOUND')
                                        Receive inbound calls
                                    @else
                                        Both inbound and outbound
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                    @error('type')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Active + Script side by side --}}
                <div class="flex items-center gap-3 pt-1">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" value="0">
                        <input wire:model="is_active" type="checkbox" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition
                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                        </div>
                    </label>
                    <span class="text-sm text-fg">Active on creation</span>
                </div>
            </div>

            {{-- Agent Script --}}
            <div class="bg-surface border border-surface rounded-xl p-6 space-y-3">
                <div>
                    <h2 class="font-semibold text-fg">Agent Script</h2>
                    <p class="text-xs text-fg-muted mt-0.5">Text shown to agents during active calls. Supports plain
                        text or HTML.</p>
                </div>
                <textarea wire:model="script" rows="7" placeholder="Enter the script agents will see during calls…"
                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted font-mono focus:outline-none focus:border-zinc-500 transition resize-y"></textarea>
            </div>

            <div class="flex justify-end">
                <button wire:click="nextStep" type="button"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                    Next: Lead Form
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 2 — Lead Form Builder
    ════════════════════════════════════════════════════════════════════════ --}}
    @if ($step === 2)
        <div x-data="{
            selectedStep: 0,
            selectedField: null,
            draggingStep: null,
            draggingField: { step: null, index: null },
            paletteType: null,
            selectField(s, f) { this.selectedStep = s;
                this.selectedField = f; },
            startPaletteDrag(t) { this.paletteType = t; },
            clearDragState() { this.draggingStep = null;
                this.draggingField = { step: null, index: null };
                this.paletteType = null; },
            startStepDrag(i) { this.draggingStep = i; },
            dropStep(i) {
                if (this.draggingStep === null) return;
                if (this.draggingStep !== i) $wire.moveLeadStepTo(this.draggingStep, i);
                this.clearDragState();
            },
            startFieldDrag(s, f) { this.draggingField = { step: s, index: f }; },
            dropField(s, f) {
                if (this.paletteType) { $wire.insertLeadFieldAt(s, f, this.paletteType);
                    this.selectField(s, f);
                    this.clearDragState(); return; }
                if (this.draggingField.step === null) return;
                if (this.draggingField.step === s) { if (this.draggingField.index !== f) $wire.moveLeadFieldTo(s, this.draggingField.index, f); } else $wire.moveLeadFieldAcrossSteps(this.draggingField.step, this.draggingField.index, s, f);
                this.selectField(s, f);
                this.clearDragState();
            },
            dropFieldAtEnd(s, end) {
                if (this.paletteType) { $wire.insertLeadFieldAt(s, end, this.paletteType);
                    this.selectField(s, Math.max(0, end));
                    this.clearDragState(); return; }
                if (this.draggingField.step === null) return;
                if (this.draggingField.step === s) $wire.moveLeadFieldTo(s, this.draggingField.index, end - 1);
                else $wire.moveLeadFieldAcrossSteps(this.draggingField.step, this.draggingField.index, s, end);
                this.selectField(s, Math.max(0, end - 1));
                this.clearDragState();
            }
        }" class="space-y-5">

            <div class="bg-surface border border-surface rounded-xl p-6 space-y-5">
                <div>
                    <h2 class="font-semibold text-fg">Lead Form Builder</h2>
                    <p class="text-xs text-fg-muted mt-0.5">Design the data capture form agents complete for each lead.
                        You can skip this step.</p>
                </div>

                {{-- Mode + Add Step --}}
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div class="space-y-1.5 min-w-[220px]">
                        <label class="block text-xs font-medium text-fg-muted">Form Mode</label>
                        <select wire:model.live="lead_process_mode"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            <option value="single">Single Form</option>
                            <option value="stepper">Stepper (multi-step)</option>
                        </select>
                    </div>
                    @if ($lead_process_mode === 'stepper' || count($lead_process_steps) === 0)
                        <button type="button" wire:click="addLeadStep"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Step
                        </button>
                    @endif
                </div>

                @if (count($lead_process_steps) === 0)
                    <div class="border-2 border-dashed border-surface rounded-xl p-10 text-center space-y-3">
                        <p class="text-sm font-medium text-fg">No form steps yet</p>
                        <p class="text-xs text-fg-muted">Add a step to start building the lead capture form, or skip to
                            Call Settings.</p>
                        <button type="button" wire:click="addLeadStep"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add First Step
                        </button>
                    </div>
                @else
                    @php
                        $fieldLibrary = [
                            ['type' => 'text', 'label' => 'Text Input'],
                            ['type' => 'textarea', 'label' => 'Long Text'],
                            ['type' => 'email', 'label' => 'Email'],
                            ['type' => 'phone', 'label' => 'Phone'],
                            ['type' => 'number', 'label' => 'Number'],
                            ['type' => 'date', 'label' => 'Date'],
                            ['type' => 'select', 'label' => 'Dropdown'],
                            ['type' => 'checkbox', 'label' => 'Checkbox'],
                        ];
                    @endphp
                    <div class="grid grid-cols-1 xl:grid-cols-[220px_minmax(0,1fr)_300px] gap-4 items-start">

                        {{-- Input Library --}}
                        <div class="bg-surface-2 border border-surface rounded-xl p-3 space-y-2 sticky top-4">
                            <p class="font-semibold text-fg text-xs">Input Library</p>
                            <p class="text-fg-muted text-[11px]">Drag to canvas or click to add to selected step</p>
                            <div class="space-y-1.5 pt-1">
                                @foreach ($fieldLibrary as $lf)
                                    <button type="button" draggable="true"
                                        @dragstart="startPaletteDrag('{{ $lf['type'] }}')"
                                        @dragend="clearDragState()"
                                        @click="$wire.addLeadFieldOfType(selectedStep, '{{ $lf['type'] }}')"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-surface bg-surface hover:bg-surface-2 text-fg text-xs transition cursor-grab active:cursor-grabbing">
                                        <span>{{ $lf['label'] }}</span>
                                        <svg class="w-3.5 h-3.5 text-fg-muted" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.75" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                        </svg>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Canvas --}}
                        <div class="space-y-4">
                            @foreach ($lead_process_steps as $si => $step)
                                <div class="bg-surface-2 border border-surface rounded-xl p-4 space-y-4"
                                    wire:key="step-{{ $si }}" @click="selectedStep = {{ $si }}"
                                    @dragover.prevent @drop.prevent="dropStep({{ $si }})"
                                    :class="[
                                        draggingStep === {{ $si }} ? 'ring-2 ring-cyan-500/40' : '',
                                        selectedStep === {{ $si }} ? 'border-cyan-500/50' : ''
                                    ]">

                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" draggable="true"
                                            @dragstart="startStepDrag({{ $si }})"
                                            @dragend="clearDragState()"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-surface bg-surface text-fg-muted hover:text-fg cursor-grab transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.75" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                            </svg>
                                        </button>
                                        <input type="text"
                                            wire:model.blur="lead_process_steps.{{ $si }}.title"
                                            placeholder="Step title"
                                            class="flex-1 min-w-[180px] bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                        <button type="button" wire:click="removeLeadStep({{ $si }})"
                                            class="px-2.5 py-2 rounded-lg border border-red-500/30 text-red-400 text-xs hover:bg-red-500/10 transition">
                                            Remove
                                        </button>
                                    </div>

                                    <div class="space-y-2 border border-dashed border-surface rounded-lg p-3"
                                        @dragover.prevent
                                        @drop.prevent="dropFieldAtEnd({{ $si }}, {{ count($step['fields'] ?? []) }})"
                                        :class="(draggingField.step !== null || paletteType) ?
                                        'border-cyan-500/40 bg-cyan-500/5' : ''">

                                        <p class="text-[11px] text-fg-muted uppercase tracking-wide">Canvas</p>

                                        @foreach ($step['fields'] ?? [] as $fi => $field)
                                            <div class="border border-surface rounded-lg p-3 space-y-2 bg-surface/50 cursor-pointer"
                                                wire:key="field-{{ $si }}-{{ $field['uid'] ?? $fi }}"
                                                @click.stop="selectField({{ $si }}, {{ $fi }})"
                                                draggable="true"
                                                @dragstart="startFieldDrag({{ $si }}, {{ $fi }})"
                                                @dragend="clearDragState()" @dragover.prevent
                                                @drop.prevent="dropField({{ $si }}, {{ $fi }})"
                                                :class="[
                                                    draggingField.step === {{ $si }} && draggingField
                                                    .index === {{ $fi }} ?
                                                    'opacity-60 ring-2 ring-cyan-500/40' : '',
                                                    selectedStep === {{ $si }} && selectedField ===
                                                    {{ $fi }} ?
                                                    'ring-2 ring-indigo-500/40 border-indigo-500/40' : ''
                                                ]">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="text-[11px] text-fg-muted uppercase">{{ $field['type'] ?? 'text' }}</span>
                                                    <button type="button"
                                                        wire:click.stop="removeLeadField({{ $si }}, {{ $fi }})"
                                                        class="text-[11px] text-red-400 hover:text-red-300 transition">remove</button>
                                                </div>
                                                <p
                                                    class="text-xs text-fg {{ $field['label'] ?? '' ? '' : 'text-fg-muted italic' }}">
                                                    {{ $field['label'] ?? '' ?: 'Untitled Field' }}
                                                    @if ($field['required'] ?? false)
                                                        <span class="text-red-400">*</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach

                                        @if (empty($step['fields']))
                                            <p class="text-xs text-fg-muted text-center py-4">Drop an input type here
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Properties panel --}}
                        <div class="bg-surface-2 border border-surface rounded-xl p-4 space-y-4 sticky top-4">
                            <p class="font-semibold text-fg text-xs">Field Properties</p>
                            @php
                                $activeField = isset($lead_process_steps[$step ?? 0]['fields'][$selectedField ?? -1])
                                    ? null
                                    : null; // resolved via Alpine selectedStep/selectedField
                            @endphp
                            @foreach ($lead_process_steps as $si => $s)
                                @foreach ($s['fields'] ?? [] as $fi => $field)
                                    <div x-show="selectedStep === {{ $si }} && selectedField === {{ $fi }}"
                                        wire:key="props-{{ $si }}-{{ $field['uid'] ?? $fi }}"
                                        class="space-y-3">

                                        <div class="space-y-1">
                                            <label class="text-[11px] text-fg-muted font-medium">Field Key</label>
                                            <input type="text"
                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.key"
                                                placeholder="e.g. first_name"
                                                class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-xs text-fg font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            @error("lead_process_steps.{$si}.fields.{$fi}.key")
                                                <p class="text-[11px] text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] text-fg-muted font-medium">Label</label>
                                            <input type="text"
                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.label"
                                                placeholder="e.g. First Name"
                                                class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] text-fg-muted font-medium">Placeholder</label>
                                            <input type="text"
                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.placeholder"
                                                class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] text-fg-muted font-medium">Help Text</label>
                                            <input type="text"
                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.help_text"
                                                class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        </div>
                                        @if (($field['type'] ?? '') === 'select')
                                            <div class="space-y-1">
                                                <label class="text-[11px] text-fg-muted font-medium">Options (one per
                                                    line)</label>
                                                <textarea wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.options_text"
                                                    rows="4"
                                                    class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none"></textarea>
                                            </div>
                                        @endif
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox"
                                                wire:model="lead_process_steps.{{ $si }}.fields.{{ $fi }}.required"
                                                class="w-4 h-4 rounded border-surface text-indigo-600">
                                            <span class="text-xs text-fg">Required</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endforeach
                            <p x-show="selectedField === null" class="text-xs text-fg-muted">Click a field on the
                                canvas to edit its properties.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <button wire:click="prevStep" type="button"
                    class="inline-flex items-center gap-2 text-sm text-fg-muted hover:text-fg border border-surface px-4 py-2 rounded-lg hover:bg-hover transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
                <button wire:click="nextStep" type="button"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                    Next: Call Settings
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 3 — Call Settings
    ════════════════════════════════════════════════════════════════════════ --}}
    @if ($step === 3)
        <div class="space-y-5">

            {{-- Dialer Settings --}}
            <div class="bg-surface border border-surface rounded-xl p-6 space-y-5">
                <div>
                    <h2 class="font-semibold text-fg">Dialer Settings</h2>
                    <p class="text-xs text-fg-muted mt-0.5">How calls are placed and queued.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Dial Mode <span
                                class="text-red-400">*</span></label>
                        <select wire:model="dial_mode"
                            class="w-full bg-surface-2 border @error('dial_mode') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            @foreach (\App\Models\Campaign::DIAL_MODES as $m)
                                <option value="{{ $m }}">{{ ucfirst(strtolower($m)) }}</option>
                            @endforeach
                        </select>
                        @error('dial_mode')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Dial Level <span
                                class="text-[11px] text-fg-muted font-normal">(predictive ratio)</span></label>
                        <input wire:model="dial_level" type="number" step="0.1" min="0.1" max="10"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                        @error('dial_level')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Outbound Caller ID</label>
                        <input wire:model="caller_id" type="text" placeholder="+1xxxxxxxxxx"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted font-mono focus:outline-none focus:border-zinc-500 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Max Simultaneous Calls</label>
                        <input wire:model="max_calls" type="number" min="1" max="100"
                            placeholder="Unlimited"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Hopper Level <span
                                class="text-[11px] text-fg-muted font-normal">(leads to pre-queue)</span></label>
                        <input wire:model="hopper_level" type="number" min="1" max="1000"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">ACW Timer <span
                                class="text-[11px] text-fg-muted font-normal">(seconds, 0 = disabled)</span></label>
                        <input wire:model="acw_seconds" type="number" min="0" max="3600"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                    </div>
                </div>
            </div>

            {{-- In-Groups --}}
            <div class="bg-surface border border-surface rounded-xl p-6 space-y-4">
                <div>
                    <h2 class="font-semibold text-fg">Inbound Groups</h2>
                    <p class="text-xs text-fg-muted mt-0.5">Select which inbound groups are routed to this campaign.
                    </p>
                </div>
                @if ($allInGroups->isEmpty())
                    <p class="text-sm text-fg-muted">No inbound groups configured yet.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($allInGroups as $ig)
                            <label
                                class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition
                            {{ in_array((string) $ig->id, array_map('strval', $selectedInGroupIds)) ? 'border-indigo-500/60 bg-indigo-500/10' : 'border-surface bg-surface-2 hover:border-zinc-600' }}">
                                <input type="checkbox" wire:model="selectedInGroupIds" value="{{ $ig->id }}"
                                    class="w-4 h-4 rounded border-surface text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                                <div class="min-w-0">
                                    <p class="text-sm text-fg font-medium truncate">{{ $ig->name }}</p>
                                    @if ($ig->description ?? false)
                                        <p class="text-[11px] text-fg-muted truncate">{{ $ig->description }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Voice & TTS --}}
            <div class="bg-surface border border-surface rounded-xl p-6 space-y-5">
                <div>
                    <h2 class="font-semibold text-fg">Voice & TTS</h2>
                    <p class="text-xs text-fg-muted mt-0.5">Text-to-speech voice, language, and call messages.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">TTS Voice</label>
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
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Greeting Message</label>
                        <input wire:model="greeting_message" type="text" placeholder="Welcome to Acme support…"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                        <p class="text-[11px] text-fg-muted">Played when caller first connects.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Hold Music URL</label>
                        <input wire:model="hold_music_url" type="url" placeholder="https://…"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                        <p class="text-[11px] text-fg-muted">Leave blank to use Twilio default.</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-xs font-medium text-fg-muted">End-of-Call Messages</p>
                    @foreach ([['key' => 'tts_completed', 'label' => 'Completed'], ['key' => 'tts_busy', 'label' => 'Busy'], ['key' => 'tts_no_answer', 'label' => 'No Answer'], ['key' => 'tts_failed', 'label' => 'Failed'], ['key' => 'tts_canceled', 'label' => 'Canceled']] as $tts)
                        <div class="flex items-center gap-3">
                            <span class="text-fg-muted text-xs w-20 shrink-0">{{ $tts['label'] }}</span>
                            <input wire:model="{{ $tts['key'] }}" type="text"
                                class="flex-1 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            @error($tts['key'])
                                <p class="text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recording --}}
            <div class="bg-surface border border-surface rounded-xl p-6 space-y-4">
                <div>
                    <h2 class="font-semibold text-fg">Recording</h2>
                    <p class="text-xs text-fg-muted mt-0.5">Configure call recording for this campaign.</p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input wire:model.live="recording_enabled" type="checkbox" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition
                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                        </div>
                    </label>
                    <span class="text-sm text-fg">Enable Call Recording</span>
                </div>
                @if ($recording_enabled)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-fg-muted">Record Channels</label>
                        <select wire:model="recording_channels"
                            class="w-64 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            @foreach (\App\Models\Campaign::REC_CHANNELS as $ch)
                                <option value="{{ $ch }}">{{ ucfirst($ch) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between">
                <button wire:click="prevStep" type="button"
                    class="inline-flex items-center gap-2 text-sm text-fg-muted hover:text-fg border border-surface px-4 py-2 rounded-lg hover:bg-hover transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
                <button wire:click="save" type="button"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
                    <span wire:loading.remove wire:target="save">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Create Campaign
                    </span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>
    @endif

</div>
