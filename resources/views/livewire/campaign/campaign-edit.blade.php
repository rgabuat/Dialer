<div class="space-y-6">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-fg-muted text-xs mb-1">
                <a href="{{ route('campaigns.index') }}" wire:navigate class="hover:text-fg transition">Campaigns</a>
                <span>/</span>
                <span class="text-fg">{{ $name ?: 'Edit Campaign' }}</span>
            </nav>
            <h1 class="font-bold text-fg text-2xl leading-tight">Edit Campaign</h1>
            <p class="text-fg-muted text-sm mt-0.5">Update campaign and dialer settings.</p>
        </div>

            <div class="flex items-center gap-2 flex-wrap">
            <button type="submit" form="campaign-form"
                class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-4 py-2 rounded-lg font-semibold text-white text-sm transition">
                <span wire:loading.remove wire:target="save"><x-heroicon-o-check class="w-4 h-4" /></span>
                <span wire:loading wire:target="save"><x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" /></span>
                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
            <a href="{{ route('campaigns.index') }}" wire:navigate
                class="inline-flex items-center px-3 py-2 rounded-lg border border-surface bg-surface-2 hover:bg-surface text-fg-muted hover:text-fg text-sm font-medium transition">
                Cancel
            </a>
        </div>
    </div>

    {{-- ── Flash messages ──────────────────────────────────────────────── --}}
    @if (session('success'))
        <div
            class="flex items-center gap-2 bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3 text-accent-green text-sm">
            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Form ─────────────────────────────────────────────────────────── --}}
    <form id="campaign-form" wire:submit.prevent="save">
        <div class="space-y-5">

                <div class="bg-surface border border-surface rounded-xl p-2">
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="$set('editor_tab', 'settings')"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ $editor_tab === 'settings' ? 'bg-fuchsia-600 text-white' : 'bg-surface-2 text-fg-muted hover:text-fg hover:bg-surface' }}">
                            <x-heroicon-o-adjustments-horizontal class="w-4 h-4" />
                            Settings
                        </button>
                        <button type="button" wire:click="$set('editor_tab', 'leadprocess')"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ $editor_tab === 'leadprocess' ? 'bg-cyan-600 text-white' : 'bg-surface-2 text-fg-muted hover:text-fg hover:bg-surface' }}">
                            <x-heroicon-o-rectangle-group class="w-4 h-4" />
                            Lead Process
                        </button>
                        <button type="button" wire:click="$set('editor_tab', 'callbacks')"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ $editor_tab === 'callbacks' ? 'bg-emerald-600 text-white' : 'bg-surface-2 text-fg-muted hover:text-fg hover:bg-surface' }}">
                            <x-heroicon-o-phone-arrow-up-right class="w-4 h-4" />
                            Callbacks
                        </button>
                    </div>
                </div>

                @if ($editor_tab === 'leadprocess')
                    <div x-data="{
                            selectedStep: 0,
                            selectedField: null,
                            draggingStep: null,
                            draggingField: { step: null, index: null },
                            paletteType: null,
                            selectField(stepIndex, fieldIndex) {
                                this.selectedStep = stepIndex;
                                this.selectedField = fieldIndex;
                            },
                            startPaletteDrag(type) { this.paletteType = type; },
                            clearDragState() {
                                this.draggingStep = null;
                                this.draggingField = { step: null, index: null };
                                this.paletteType = null;
                            },
                            startStepDrag(index) { this.draggingStep = index; },
                            dropStep(index) {
                                if (this.draggingStep === null) return;
                                if (this.draggingStep !== index) {
                                    $wire.moveLeadStepTo(this.draggingStep, index);
                                }
                                this.clearDragState();
                            },
                            startFieldDrag(stepIndex, fieldIndex) {
                                this.draggingField = { step: stepIndex, index: fieldIndex };
                            },
                            dropField(stepIndex, fieldIndex) {
                                if (this.paletteType) {
                                    $wire.insertLeadFieldAt(stepIndex, fieldIndex, this.paletteType);
                                    this.selectedStep = stepIndex;
                                    this.selectedField = fieldIndex;
                                    this.clearDragState();
                                    return;
                                }

                                if (this.draggingField.step === null) return;
                                if (this.draggingField.step === stepIndex) {
                                    if (this.draggingField.index !== fieldIndex) {
                                        $wire.moveLeadFieldTo(stepIndex, this.draggingField.index, fieldIndex);
                                    }
                                } else {
                                    $wire.moveLeadFieldAcrossSteps(this.draggingField.step, this.draggingField.index, stepIndex, fieldIndex);
                                }
                                this.selectField(stepIndex, fieldIndex);
                                this.clearDragState();
                            },
                            dropFieldAtEnd(stepIndex, endIndex) {
                                if (this.paletteType) {
                                    $wire.insertLeadFieldAt(stepIndex, endIndex, this.paletteType);
                                    this.selectedStep = stepIndex;
                                    this.selectedField = Math.max(0, endIndex);
                                    this.clearDragState();
                                    return;
                                }

                                if (this.draggingField.step === null) return;
                                if (this.draggingField.step === stepIndex) {
                                    $wire.moveLeadFieldTo(stepIndex, this.draggingField.index, endIndex - 1);
                                } else {
                                    $wire.moveLeadFieldAcrossSteps(this.draggingField.step, this.draggingField.index, stepIndex, endIndex);
                                }
                                this.selectField(stepIndex, Math.max(0, endIndex - 1));
                                this.clearDragState();
                            }
                        }"
                        class="bg-surface border border-surface rounded-xl overflow-hidden">
                        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-cyan-500/15 shrink-0">
                                <x-heroicon-s-list-bullet class="w-3.5 h-3.5 text-cyan-400" />
                            </div>
                            <h2 class="font-semibold text-fg text-sm">Lead Process</h2>
                            <p class="text-fg-muted text-xs ml-auto hidden sm:block">Real canvas builder with field properties panel</p>
                        </div>

                        <div class="p-5 space-y-5">
                            <div class="flex flex-wrap items-end justify-between gap-3">
                                <div class="w-full md:w-auto md:min-w-[260px]">
                                    <label class="block mb-1.5 font-semibold text-fg text-xs">Form Mode</label>
                                    <select wire:model.change="lead_process_mode"
                                        class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                        <option value="single">Single Form</option>
                                        <option value="stepper">Stepper</option>
                                    </select>
                                    @error('lead_process_mode')
                                        <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($lead_process_mode === 'stepper' || count($lead_process_steps) === 0)
                                        <button type="button" wire:click="addLeadStep"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-xs">
                                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                            Add Step
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-surface-2 border border-surface rounded-lg p-3 text-xs text-fg-muted">
                                Drag any input from Input Library to Form Canvas. Click a canvas input to edit its properties on the right.
                            </div>

                            @error('lead_process_steps')
                                <p class="text-accent-red text-xs">{{ $message }}</p>
                            @enderror

                            @if (count($lead_process_steps) === 0)
                                <div class="border border-dashed border-surface rounded-lg p-6 text-center space-y-3">
                                    <p class="text-fg text-sm font-medium">Start by adding the first step.</p>
                                    <p class="text-fg-muted text-xs">Then drag input blocks onto the canvas to design your form.</p>
                                    <button type="button" wire:click="addLeadStep"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-xs">
                                        <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                        Add First Step
                                    </button>
                                </div>
                            @endif

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

                            <div class="grid grid-cols-1 xl:grid-cols-[250px_minmax(0,1fr)_320px] gap-4 items-start">
                                <div class="bg-surface-2 border border-surface rounded-xl p-3 space-y-3 sticky top-4">
                                    <div>
                                        <p class="font-semibold text-fg text-xs">Input Library</p>
                                        <p class="text-fg-muted text-xs mt-0.5">Drag input type to canvas</p>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach ($fieldLibrary as $libraryField)
                                            <button type="button"
                                                draggable="true"
                                                @dragstart="startPaletteDrag('{{ $libraryField['type'] }}')"
                                                @dragend="clearDragState()"
                                                @click="$wire.addLeadFieldOfType(selectedStep, '{{ $libraryField['type'] }}')"
                                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-surface bg-surface hover:bg-surface-2 text-fg text-xs transition text-left cursor-grab active:cursor-grabbing">
                                                <span>{{ $libraryField['label'] }}</span>
                                                <x-heroicon-o-bars-3 class="w-3.5 h-3.5 text-fg-muted" />
                                            </button>
                                        @endforeach
                                    </div>
                                    <p class="text-fg-muted/80 text-[11px]">Tip: click a type to add to selected step.</p>
                                </div>

                                <div class="space-y-4">
                                    @foreach ($lead_process_steps as $stepIndex => $step)
                                        <div class="bg-surface-2 border border-surface rounded-xl p-4 space-y-4"
                                            wire:key="lead-step-{{ $stepIndex }}"
                                            @click="selectedStep = {{ $stepIndex }}"
                                            @dragover.prevent
                                            @drop.prevent="dropStep({{ $stepIndex }})"
                                            :class="[
                                                draggingStep === {{ $stepIndex }} ? 'ring-2 ring-cyan-500/40' : '',
                                                selectedStep === {{ $stepIndex }} ? 'border-cyan-500/50' : ''
                                            ]">

                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button" draggable="true"
                                                    @dragstart="startStepDrag({{ $stepIndex }})"
                                                    @dragend="clearDragState()"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-surface bg-surface text-fg-muted hover:text-fg cursor-grab active:cursor-grabbing transition"
                                                    title="Drag step">
                                                    <x-heroicon-o-bars-3 class="w-4 h-4" />
                                                </button>
                                                <input type="text" wire:model.blur="lead_process_steps.{{ $stepIndex }}.title"
                                                    placeholder="Step title"
                                                    class="flex-1 min-w-[220px] bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                                <button type="button" wire:click="removeLeadStep({{ $stepIndex }})"
                                                    class="inline-flex items-center px-2.5 py-2 rounded-lg border border-red-500/30 text-accent-red text-xs hover:bg-red-500/10 transition">
                                                    Remove Step
                                                </button>
                                            </div>

                                            @error("lead_process_steps.$stepIndex.title")
                                                <p class="text-accent-red text-xs">{{ $message }}</p>
                                            @enderror

                                            <div class="space-y-3 border border-dashed border-surface rounded-lg p-3"
                                                @dragover.prevent
                                                @drop.prevent="dropFieldAtEnd({{ $stepIndex }}, {{ count($step['fields'] ?? []) }})"
                                                :class="(draggingField.step !== null || paletteType) ? 'border-cyan-500/40 bg-cyan-500/5' : ''">

                                                <div class="text-fg-muted text-[11px] uppercase tracking-wide">Form Canvas</div>

                                                @foreach (($step['fields'] ?? []) as $fieldIndex => $field)
                                                    <div class="border border-surface rounded-lg p-3 space-y-2 bg-surface/50 cursor-pointer"
                                                        wire:key="lead-field-{{ $stepIndex }}-{{ $field['uid'] ?? $fieldIndex }}"
                                                        @click.stop="selectField({{ $stepIndex }}, {{ $fieldIndex }})"
                                                        draggable="true"
                                                        @dragstart="startFieldDrag({{ $stepIndex }}, {{ $fieldIndex }})"
                                                        @dragend="clearDragState()"
                                                        @dragover.prevent
                                                        @drop.prevent="dropField({{ $stepIndex }}, {{ $fieldIndex }})"
                                                        :class="[
                                                            draggingField.step === {{ $stepIndex }} && draggingField.index === {{ $fieldIndex }} ? 'opacity-60 ring-2 ring-cyan-500/40' : '',
                                                            selectedStep === {{ $stepIndex }} && selectedField === {{ $fieldIndex }} ? 'ring-2 ring-fuchsia-500/40 border-fuchsia-500/40' : ''
                                                        ]">

                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="inline-flex items-center gap-1.5 text-fg-muted text-xs">
                                                                <x-heroicon-o-bars-3 class="w-3.5 h-3.5" />
                                                                {{ strtoupper((string) ($field['type'] ?? 'text')) }}
                                                            </div>
                                                            <div class="text-fg-muted text-[11px]">Click to edit properties</div>
                                                        </div>

                                                        <div>
                                                            <label class="block text-fg-muted text-xs mb-1">
                                                                {{ ($field['label'] ?? '') !== '' ? $field['label'] : 'Untitled Field' }}
                                                                @if (($field['required'] ?? false) === true)
                                                                    <span class="text-accent-red">*</span>
                                                                @endif
                                                            </label>

                                                            @if (($field['type'] ?? 'text') === 'textarea')
                                                                <textarea disabled rows="2" placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                    class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg-muted text-xs resize-none"></textarea>
                                                            @elseif (($field['type'] ?? 'text') === 'select')
                                                                <select disabled
                                                                    class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg-muted text-xs">
                                                                    <option value="">{{ $field['placeholder'] ?? 'Select option' }}</option>
                                                                </select>
                                                            @elseif (($field['type'] ?? 'text') === 'checkbox')
                                                                <label class="inline-flex items-center gap-2 text-fg-muted text-xs">
                                                                    <input type="checkbox" disabled class="w-4 h-4 rounded border-surface" />
                                                                    <span>{{ ($field['placeholder'] ?? '') !== '' ? $field['placeholder'] : (($field['label'] ?? '') !== '' ? $field['label'] : 'Checkbox') }}</span>
                                                                </label>
                                                            @else
                                                                <input type="text" disabled placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                    class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg-muted text-xs" />
                                                            @endif

                                                            @if (($field['help_text'] ?? '') !== '')
                                                                <p class="text-fg-muted/80 text-[11px] mt-1">{{ $field['help_text'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @if (count($step['fields'] ?? []) === 0)
                                                    <div class="text-center py-5 text-fg-muted text-xs border border-dashed border-surface rounded-lg">
                                                        Drop input fields here
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="bg-surface-2 border border-surface rounded-xl p-4 space-y-3 sticky top-4">
                                    <div>
                                        <p class="font-semibold text-fg text-xs">Field Properties</p>
                                        <p class="text-fg-muted text-xs mt-0.5">Select canvas field to edit</p>
                                    </div>

                                    <div x-show="selectedField === null" class="text-fg-muted text-xs py-6 text-center border border-dashed border-surface rounded-lg">
                                        Click a field in the form canvas
                                    </div>

                                    @foreach ($lead_process_steps as $stepIndex => $step)
                                        @foreach (($step['fields'] ?? []) as $fieldIndex => $field)
                                            <div x-cloak x-show="selectedStep === {{ $stepIndex }} && selectedField === {{ $fieldIndex }}" class="space-y-3">
                                                <div>
                                                    <label class="block mb-1 text-fg-muted text-xs">Field Key</label>
                                                    <input type="text" wire:model.blur="lead_process_steps.{{ $stepIndex }}.fields.{{ $fieldIndex }}.key"
                                                        placeholder="e.g. first_name"
                                                        class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                                    @error("lead_process_steps.$stepIndex.fields.$fieldIndex.key")
                                                        <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="block mb-1 text-fg-muted text-xs">Label</label>
                                                    <input type="text" wire:model.blur="lead_process_steps.{{ $stepIndex }}.fields.{{ $fieldIndex }}.label"
                                                        placeholder="Visible field label"
                                                        class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                                    @error("lead_process_steps.$stepIndex.fields.$fieldIndex.label")
                                                        <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="block mb-1 text-fg-muted text-xs">Type</label>
                                                    <select wire:model.change="lead_process_steps.{{ $stepIndex }}.fields.{{ $fieldIndex }}.type"
                                                        class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                                        <option value="text">Text</option>
                                                        <option value="textarea">Textarea</option>
                                                        <option value="email">Email</option>
                                                        <option value="phone">Phone</option>
                                                        <option value="number">Number</option>
                                                        <option value="date">Date</option>
                                                        <option value="select">Select</option>
                                                        <option value="checkbox">Checkbox</option>
                                                    </select>
                                                </div>

                                                <div class="flex items-center justify-between gap-2 border border-surface rounded-lg px-3 py-2 bg-surface">
                                                    <div>
                                                        <p class="text-fg text-xs font-medium">Optional / Required</p>
                                                        <p class="text-fg-muted text-[11px]">Enable required if field must be filled</p>
                                                    </div>
                                                    <label class="inline-flex items-center gap-2 text-fg text-xs">
                                                        <input type="checkbox" wire:model.change="lead_process_steps.{{ $stepIndex }}.fields.{{ $fieldIndex }}.required"
                                                            class="w-4 h-4 rounded text-cyan-600 border-surface-2 focus:ring-cyan-500 focus:ring-offset-0">
                                                        Required
                                                    </label>
                                                </div>

                                                <div>
                                                    <label class="block mb-1 text-fg-muted text-xs">Placeholder</label>
                                                    <input type="text" wire:model.blur="lead_process_steps.{{ $stepIndex }}.fields.{{ $fieldIndex }}.placeholder"
                                                        placeholder="Text shown inside input"
                                                        class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                                </div>

                                                <div>
                                                    <label class="block mb-1 text-fg-muted text-xs">Help Text</label>
                                                    <input type="text" wire:model.blur="lead_process_steps.{{ $stepIndex }}.fields.{{ $fieldIndex }}.help_text"
                                                        placeholder="Short guidance shown below the field"
                                                        class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                                </div>

                                                @if (($field['type'] ?? 'text') === 'select')
                                                    <div>
                                                        <label class="block mb-1 text-fg-muted text-xs">Select Options (one per line)</label>
                                                        <textarea wire:model.blur="lead_process_steps.{{ $stepIndex }}.fields.{{ $fieldIndex }}.options_text"
                                                            rows="3"
                                                            class="w-full bg-surface border border-surface rounded-lg px-3 py-2 text-fg text-sm resize-y focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                                            placeholder="Option A&#10;Option B&#10;Option C"></textarea>
                                                    </div>
                                                @endif

                                                <button type="button" wire:click="removeLeadField({{ $stepIndex }}, {{ $fieldIndex }})"
                                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border border-red-500/30 text-accent-red text-xs hover:bg-red-500/10 transition">
                                                    <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                                    Remove Field
                                                </button>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Settings tab --}}
                @if ($editor_tab === 'settings')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                <div class="space-y-5">

                {{-- Name / Description / Active --}}
                <div class="bg-surface border border-surface rounded-xl p-5 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block mb-1 text-fg-muted text-xs font-semibold">Campaign Name <span class="text-accent-red">*</span></label>
                            <input wire:model.defer="name" type="text" placeholder="e.g. Summer Outbound"
                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                            @error('name') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-fg-muted text-xs font-semibold">Description <span class="font-normal">(optional)</span></label>
                            <input wire:model.defer="description" type="text" placeholder="Short description…"
                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                            @error('description') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" wire:model.defer="is_active" class="sr-only peer">
                                    <div class="w-9 h-5 rounded-full bg-zinc-600 peer-checked:bg-emerald-500 transition-colors"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                                </div>
                                <span class="text-fg text-sm font-medium">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Dialer Settings --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500/15 shrink-0">
                            <x-heroicon-s-phone class="w-3 h-3 text-blue-400" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">Dialer</h2>
                    </div>
                    <div class="divide-y divide-surface">

                        {{-- Row 1: Type + Mode --}}
                        <div class="grid grid-cols-2 gap-px bg-surface">
                            <div class="bg-surface-2 px-4 py-3 space-y-1.5">
                                <label class="block text-fg-muted text-xs font-semibold">Type</label>
                                <select wire:model.defer="type" class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                    <option value="OUTBOUND">Outbound</option>
                                    <option value="INBOUND">Inbound</option>
                                    <option value="BLENDED">Blended</option>
                                </select>
                                @error('type') <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="bg-surface-2 px-4 py-3 space-y-1.5">
                                <label class="block text-fg-muted text-xs font-semibold">Dial Mode</label>
                                <select wire:model.defer="dial_mode" class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                    <option value="MANUAL">Manual</option>
                                    <option value="PREVIEW">Preview</option>
                                    <option value="PROGRESSIVE">Progressive</option>
                                    <option value="PREDICTIVE">Predictive</option>
                                </select>
                                @error('dial_mode') <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Row 2: Caller ID + Hopper --}}
                        <div class="grid grid-cols-2 gap-px bg-surface">
                            <div class="bg-surface-2 px-4 py-3 space-y-1.5">
                                <label class="block text-fg-muted text-xs font-semibold">Outbound Caller ID</label>
                                <input wire:model.defer="caller_id" type="text" placeholder="+1… (blank = default)"
                                    class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-fg placeholder:text-fg-muted/40 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('caller_id') <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="bg-surface-2 px-4 py-3 space-y-1.5">
                                <label class="block text-fg-muted text-xs font-semibold">Hopper Level <span class="font-normal text-fg-muted/60">(leads pre-queued)</span></label>
                                <input wire:model.defer="hopper_level" type="number" min="1" max="1000"
                                    class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('hopper_level') <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Row 3: Dial Level + Max Calls + ACW --}}
                        <div class="grid grid-cols-3 gap-px bg-surface">
                            <div class="bg-surface-2 px-4 py-3 space-y-1.5">
                                <label class="block text-fg-muted text-xs font-semibold">Dial Level <span class="font-normal text-fg-muted/60">(ratio)</span></label>
                                <input wire:model.defer="dial_level" type="number" step="0.1" min="0.1" max="10"
                                    class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('dial_level') <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="bg-surface-2 px-4 py-3 space-y-1.5">
                                <label class="block text-fg-muted text-xs font-semibold">Max Calls <span class="font-normal text-fg-muted/60">(simultaneous)</span></label>
                                <input wire:model.defer="max_calls" type="number" min="1" max="100" placeholder="Unlimited"
                                    class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('max_calls') <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="bg-surface-2 px-4 py-3 space-y-1.5">
                                <label class="block text-fg-muted text-xs font-semibold">ACW Timer <span class="font-normal text-fg-muted/60">(seconds)</span></label>
                                <input wire:model.defer="acw_seconds" type="number" min="0" max="3600"
                                    class="w-full bg-surface border border-surface rounded-lg px-2.5 py-1.5 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('acw_seconds') <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                </div>{{-- /left col --}}
                <div class="space-y-5">{{-- right col --}}

                {{-- ── Inbound Groups ── --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500/15 shrink-0">
                            <x-heroicon-s-funnel class="w-3.5 h-3.5 text-indigo-400" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">Inbound Groups</h2>
                        <p class="text-fg-muted text-xs ml-auto hidden sm:block">Route inbound calls to this campaign
                        </p>
                    </div>
                    <div class="p-5">
                        @if ($allInGroups->isEmpty())
                            <p class="text-fg-muted text-sm">No inbound groups found. <a
                                    href="{{ route('in-group.create') }}" wire:navigate
                                    class="text-indigo-400 hover:underline">Create one</a> first.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($allInGroups as $inGroup)
                                    <label
                                        class="flex items-center gap-3 bg-surface-2 hover:bg-hover border border-surface rounded-lg px-3 py-2.5 cursor-pointer transition group">
                                        <input type="checkbox" wire:model="selectedInGroupIds"
                                            value="{{ $inGroup->id }}"
                                            class="w-4 h-4 rounded text-indigo-600 border-surface focus:ring-indigo-500 focus:ring-offset-0 shrink-0">
                                        <div class="min-w-0">
                                            <p class="text-fg text-sm font-medium leading-tight truncate">
                                                {{ $inGroup->name }}</p>
                                            @if ($inGroup->description)
                                                <p class="text-fg-muted text-xs leading-tight truncate mt-0.5">
                                                    {{ $inGroup->description }}</p>
                                            @endif
                                        </div>
                                        @if (!$inGroup->is_active)
                                            <span
                                                class="ml-auto shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-surface text-fg-muted border border-surface">Inactive</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-fg-muted/60 text-xs mt-3">
                                Selected: <span class="text-fg font-medium">{{ count($selectedInGroupIds) }}</span> of
                                {{ $allInGroups->count() }}
                            </p>
                        @endif
                        @error('selectedInGroupIds')
                            <p class="text-accent-red text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                {{-- Agent Script --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-yellow-500/15 shrink-0">
                            <x-heroicon-s-document-text class="w-3.5 h-3.5 text-accent-yellow" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">Agent Script</h2>
                        <p class="text-fg-muted text-xs ml-auto hidden sm:block">Shown to agents during active calls</p>
                    </div>
                    <div class="p-5">
                        <textarea wire:model.defer="script" rows="12" placeholder="Enter the script agents will see during calls..."
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm font-mono resize-y focus:outline-none focus:ring-2 focus:ring-fuchsia-500"></textarea>
                        @error('script')
                            <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                </div>{{-- /right col --}}
                </div>{{-- /settings grid --}}
                @endif

                {{-- Callbacks tab --}}
                @if ($editor_tab === 'callbacks')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                <div class="space-y-5">

                {{-- ── CID Rotation ── --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-emerald-500/15 shrink-0">
                            <x-heroicon-s-arrow-path class="w-3.5 h-3.5 text-emerald-400" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">CID Rotation</h2>
                        <p class="text-fg-muted text-xs ml-auto hidden sm:block">Rotate caller IDs on outbound calls
                        </p>
                    </div>
                    <div class="p-5 space-y-4">

                        <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                            <div class="relative">
                                <input wire:model.live="cid_rotation" type="checkbox" class="sr-only peer">
                                <div class="w-9 h-5 rounded-full bg-zinc-600 peer-checked:bg-emerald-500 transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-fg text-sm font-medium">Enable CID Rotation</span>
                            <span class="text-fg-muted text-xs">(overrides fixed Caller ID)</span>
                        </label>

                        @if ($cid_rotation)
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">
                                    CID Group
                                    <span class="font-normal text-fg-muted ml-1">— numbers in this group are exclusive to this campaign</span>
                                </label>
                                <select wire:model.defer="cid_group_id"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="">— No group selected (use fixed Caller ID) —</option>
                                    @foreach ($allCidGroups as $group)
                                        @php
                                            $boundToOther = $group->campaign && $group->campaign->id !== $campaign->id;
                                        @endphp
                                        <option value="{{ $group->id }}"
                                            {{ $boundToOther ? 'disabled' : '' }}
                                            {{ (int) $cid_group_id === $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                            ({{ $group->cidNumbers->count() }} number{{ $group->cidNumbers->count() !== 1 ? 's' : '' }})
                                            @if ($boundToOther) — bound to {{ $group->campaign->name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('cid_group_id')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-fg-muted/60 text-xs mt-1.5">
                                    Only active, unbound groups are shown. Manage groups on the
                                    <a href="{{ route('cid-groups.index') }}" wire:navigate class="text-emerald-400 hover:underline font-medium">CID Groups</a> page.
                                    The fixed Outbound Caller ID is used as fallback when the group is empty.
                                </p>
                            </div>
                        @else
                            <p class="text-fg-muted/60 text-xs">When disabled, the <span class="text-fg">Outbound
                                    Caller ID</span> field above is used for all calls.</p>
                        @endif

                    </div>
                </div>

                </div>{{-- /left col --}}
                <div class="space-y-5">{{-- right col --}}

                {{-- Voice & Recording --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500/15 shrink-0">
                            <x-heroicon-s-speaker-wave class="w-3.5 h-3.5 text-indigo-400" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">Voice & Recording</h2>
                        <p class="text-fg-muted text-xs ml-auto hidden sm:block">TTS messages, hold music, and call
                            recording</p>
                    </div>
                    <div class="p-5 space-y-5">

                        {{-- TTS voice + language --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">TTS Voice</label>
                                <select wire:model.defer="tts_voice"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                    <option value="alice">Alice (neural)</option>
                                    <option value="man">Man</option>
                                    <option value="woman">Woman</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Language</label>
                                <select wire:model.defer="tts_language"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
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

                        {{-- Greeting + Hold music --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Greeting Message <span
                                        class="font-normal text-fg-muted">(optional)</span></label>
                                <input wire:model.defer="greeting_message" type="text"
                                    placeholder="Welcome to Acme support..."
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                <p class="text-fg-muted/60 text-xs mt-1">Played when caller first connects.</p>
                                @error('greeting_message')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Hold Music URL <span
                                        class="font-normal text-fg-muted">(blank = Twilio default)</span></label>
                                <input wire:model.defer="hold_music_url" type="url" placeholder="https://..."
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('hold_music_url')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- TTS messages --}}
                        <div>
                            <p class="font-semibold text-fg text-xs mb-3">End-of-Call Messages <span
                                    class="font-normal text-fg-muted">(spoken to caller based on outcome)</span></p>
                            <div class="space-y-3">
                                @foreach ([['key' => 'tts_completed', 'label' => 'Completed'], ['key' => 'tts_busy', 'label' => 'Busy'], ['key' => 'tts_no_answer', 'label' => 'No Answer'], ['key' => 'tts_failed', 'label' => 'Failed'], ['key' => 'tts_canceled', 'label' => 'Canceled']] as $tts)
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="text-fg-muted text-xs w-24 pt-2.5 shrink-0">{{ $tts['label'] }}</span>
                                        <div class="flex-1">
                                            <input wire:model.defer="{{ $tts['key'] }}" type="text"
                                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                            @error($tts['key'])
                                                <p class="text-accent-red text-xs mt-0.5">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Recording --}}
                        <div class="border-t border-surface pt-4 flex flex-wrap items-center gap-4">
                            <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                                <div class="relative">
                                    <input wire:model.live="recording_enabled" type="checkbox" class="sr-only peer">
                                    <div class="w-9 h-5 rounded-full bg-zinc-600 peer-checked:bg-fuchsia-500 transition-colors"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                                </div>
                                <span class="text-fg text-sm font-medium">Record Calls</span>
                            </label>
                            @if ($recording_enabled)
                                <select wire:model.defer="recording_channels"
                                    class="bg-surface-2 border border-surface rounded-lg px-3 py-1.5 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                    <option value="both">Both channels</option>
                                    <option value="inbound">Inbound only</option>
                                    <option value="outbound">Outbound only</option>
                                </select>
                            @endif
                        </div>

                    </div>
                </div>

                </div>{{-- /right col --}}
                </div>{{-- /callbacks grid --}}
                @endif



        </div>
    </form>

</div>
