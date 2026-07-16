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
                    <span class="text-xs text-fg-muted">Lead Templates</span>
                    <span class="text-fg-muted">/</span>
                    <span class="text-sm font-semibold text-fg">{{ $mode === 'create' ? 'New Template' : $name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="save" wire:loading.attr="disabled" type="button"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition">
                        Save
                    </button>
                    <button wire:click="close" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-400 hover:text-white hover:bg-red-500 border border-red-500/30 hover:border-red-500 text-sm font-medium transition">
                        Cancel
                    </button>
                </div>
            </div>

            {{-- Sidebar + content --}}
            <div class="flex flex-1 min-h-0">

                {{-- Sidebar stepper --}}
                <aside class="w-48 shrink-0 border-r border-surface bg-surface flex flex-col py-4 px-3 gap-0.5">
                    @foreach ([['n' => 1, 'label' => 'Details'], ['n' => 2, 'label' => 'Form Builder']] as $s)
                        <button type="button" wire:click="$set('step', {{ $s['n'] }})"
                            @class([
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

                    {{-- STEP 1: DETAILS --}}
                    @if ($step === 1)
                        <div class="max-w-2xl mx-auto px-8 py-6 space-y-5">
                            <div>
                                <h2 class="font-bold text-fg">Template Details</h2>
                                <p class="text-xs text-fg-muted mt-0.5">Name and description for this lead form
                                    template.</p>
                            </div>

                            @if ($errors->any())
                                <div
                                    class="flex items-start gap-2 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 text-red-400 text-xs">
                                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                    Fix the errors below before saving.
                                </div>
                            @endif

                            <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-fg-muted">Name <span
                                            class="text-red-400">*</span></label>
                                    <input wire:model="name" type="text" placeholder="e.g. Storage Lead Form"
                                        @class([
                                            'w-full bg-surface-2 border rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition',
                                            'border-red-500' => $errors->has('name'),
                                            'border-surface' => !$errors->has('name'),
                                        ])>
                                    @error('name')
                                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-fg-muted">Description</label>
                                    <textarea wire:model="description" rows="3" placeholder="Optional description..."
                                        class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition resize-none"></textarea>
                                </div>

                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                        <div
                                            class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                        </div>
                                    </label>
                                    <span class="text-sm text-fg">Active</span>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button wire:click="nextStep" type="button"
                                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                                    Next: Form Builder
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- STEP 2: FORM BUILDER --}}
                    @if ($step === 2)
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
                        }" class="h-full flex flex-col">

                            {{-- Builder toolbar --}}
                            <div class="flex items-center justify-between px-6 py-3 border-b border-surface shrink-0">
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
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Add Step
                                        </button>
                                    @endif
                                </div>
                                <button wire:click="prevStep" type="button"
                                    class="inline-flex items-center gap-1.5 text-sm text-fg-muted hover:text-fg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                    </svg>
                                    Back to Details
                                </button>
                            </div>

                            @if (count($lead_process_steps) === 0)
                                <div class="flex-1 flex items-center justify-center">
                                    <div class="text-center space-y-3">
                                        <p class="text-sm text-fg-muted">No steps yet — add a step to start building.
                                        </p>
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
                                </div>
                            @else
                                <div class="flex-1 overflow-y-auto">
                                    <div class="grid grid-cols-[220px_minmax(0,1fr)_300px] gap-5 items-start p-6">

                                        {{-- Palette --}}
                                        <div
                                            class="bg-surface border border-surface rounded-xl p-3 space-y-2 sticky top-0">
                                            <p class="text-xs font-semibold text-fg">Input Library</p>
                                            <p class="text-[11px] text-fg-muted">Drag to canvas or click to add</p>
                                            <div class="space-y-1">
                                                @foreach ($fieldLibrary as $lf)
                                                    <button type="button" draggable="true"
                                                        @dragstart="palette='{{ $lf['type'] }}'"
                                                        @dragend="clearDrag()"
                                                        @click="$wire.addLeadFieldOfType(sel.step,'{{ $lf['type'] }}')"
                                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg border border-surface bg-surface-2 hover:bg-hover text-xs text-fg transition cursor-grab">
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
                                                    wire:key="lt-step-{{ $si }}"
                                                    @click="sel.step={{ $si }}"
                                                    :class="sel.step === {{ $si }} ? 'border-cyan-500/50' : ''">
                                                    <div class="flex items-center gap-2">
                                                        <input type="text"
                                                            wire:model.blur="lead_process_steps.{{ $si }}.title"
                                                            placeholder="Step title" @class([
                                                                'flex-1 bg-surface-2 border rounded-lg px-2.5 py-1.5 text-sm text-fg focus:outline-none focus:ring-1 focus:ring-cyan-500',
                                                                'border-red-500' => $errors->has("lead_process_steps.{$si}.title"),
                                                                'border-surface' => !$errors->has("lead_process_steps.{$si}.title"),
                                                            ])>
                                                        <button type="button"
                                                            wire:click="removeLeadStep({{ $si }})"
                                                            class="text-xs text-red-400 hover:text-red-300 px-2 py-1.5 rounded-lg hover:bg-red-500/10 transition">Remove</button>
                                                    </div>
                                                    @error("lead_process_steps.{$si}.title")
                                                        <p class="text-[11px] text-red-400 -mt-1">{{ $message }}</p>
                                                    @enderror
                                                    <div class="space-y-2 min-h-[60px] border border-dashed border-surface rounded-lg p-2"
                                                        @dragover.prevent
                                                        @drop.prevent="dropEnd({{ $si }},{{ count($stepData['fields'] ?? []) }})"
                                                        :class="(drag.field !== null || palette) ?
                                                        'border-cyan-500/40 bg-cyan-500/5' : ''">
                                                        @foreach ($stepData['fields'] ?? [] as $fi => $field)
                                                            <div wire:key="lt-field-{{ $si }}-{{ $field['uid'] ?? $fi }}"
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
                                                                    class="text-[10px] text-red-400 hover:text-red-300 ml-2 shrink-0">x</button>
                                                            </div>
                                                        @endforeach
                                                        @if (empty($stepData['fields']))
                                                            <p class="text-[11px] text-fg-muted text-center py-4">Drop
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
                                                        wire:key="lt-props-{{ $si }}-{{ $field['uid'] ?? $fi }}"
                                                        class="space-y-2.5">
                                                        <div>
                                                            <label class="text-[11px] text-fg-muted">Key</label>
                                                            <input type="text"
                                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.key"
                                                                placeholder="e.g. first_name"
                                                                class="w-full bg-surface-2 border border-surface rounded px-2 py-1 text-xs font-mono text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 mt-0.5">
                                                            @error("lead_process_steps.{$si}.fields.{$fi}.key")
                                                                <p class="text-[10px] text-red-400">{{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>
                                                        <div>
                                                            <label class="text-[11px] text-fg-muted">Label</label>
                                                            <input type="text"
                                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.label"
                                                                class="w-full bg-surface-2 border border-surface rounded px-2 py-1 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 mt-0.5">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="text-[11px] text-fg-muted">Placeholder</label>
                                                            <input type="text"
                                                                wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.placeholder"
                                                                class="w-full bg-surface-2 border border-surface rounded px-2 py-1 text-xs text-fg focus:outline-none focus:ring-1 focus:ring-indigo-500 mt-0.5">
                                                        </div>
                                                        @if (($field['type'] ?? '') === 'select')
                                                            <div>
                                                                <label class="text-[11px] text-fg-muted">Options (one
                                                                    per line)</label>
                                                                <textarea wire:model.blur="lead_process_steps.{{ $si }}.fields.{{ $fi }}.options_text"
                                                                    rows="4"
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
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>
