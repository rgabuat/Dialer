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
                    <span class="text-xs text-fg-muted">CID Groups</span>
                    <span class="text-fg-muted">/</span>
                    <span
                        class="text-sm font-semibold text-fg">{{ $mode === 'create' ? 'New CID Group' : $name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="save" wire:loading.attr="disabled" type="button"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition">
                        <span wire:loading.remove wire:target="save">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-0.5" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            {{ $mode === 'create' ? 'Create Group' : 'Save Changes' }}
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
                    @foreach ([['n' => 1, 'label' => 'Details'], ['n' => 2, 'label' => 'CID Numbers']] as $s)
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

                {{-- Content --}}
                <div class="flex-1 overflow-y-auto">

                    {{-- STEP 1: DETAILS --}}
                    @if ($step === 1)
                        <div class="max-w-2xl mx-auto px-8 py-6 space-y-5">
                            <div>
                                <h2 class="font-bold text-fg">Group Details</h2>
                                <p class="text-xs text-fg-muted mt-0.5">Name and settings for this CID group.</p>
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
                                    <input wire:model="name" type="text" placeholder="e.g. Sales Team CIDs"
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
                                    <textarea wire:model="description" rows="3" placeholder="Optional description…"
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
                                    Next: Assign Numbers
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- STEP 2: CID NUMBERS --}}
                    @if ($step === 2)
                        <div class="max-w-3xl mx-auto px-8 py-6 space-y-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="font-bold text-fg">Assign CID Numbers</h2>
                                    <p class="text-xs text-fg-muted mt-0.5">Select which phone numbers belong to this
                                        group for round-robin rotation.</p>
                                </div>
                                <button wire:click="prevStep" type="button"
                                    class="text-sm text-fg-muted hover:text-fg transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                    </svg>
                                    Back
                                </button>
                            </div>

                            @error('selectedCidIds')
                                <div
                                    class="flex items-start gap-2 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 text-red-400 text-xs">
                                    {{ $message }}
                                </div>
                            @enderror

                            @if ($allNumbers->isEmpty())
                                <div class="bg-surface border border-surface rounded-xl p-10 text-center">
                                    <p class="text-sm text-fg-muted">No CID numbers configured yet.</p>
                                    <p class="text-xs text-fg-muted mt-1">Add CID numbers to the system first.</p>
                                </div>
                            @else
                                <div class="space-y-2">
                                    {{-- Summary --}}
                                    <div class="flex items-center justify-between text-xs text-fg-muted px-1">
                                        <span>{{ count($selectedCidIds) }} of {{ $allNumbers->count() }} selected</span>
                                        @if (count($selectedCidIds) > 0)
                                            <button type="button" wire:click="$set('selectedCidIds', [])"
                                                class="text-red-400 hover:text-red-300 transition">Clear all</button>
                                        @endif
                                    </div>

                                    {{-- Number list --}}
                                    @foreach ($allNumbers as $num)
                                        @php
                                            $checked = in_array((string) $num->id, $selectedCidIds);
                                            $otherGroup =
                                                !$checked && $num->cid_group_id && $num->cid_group_id !== $groupId;
                                        @endphp
                                        <label @class([
                                            'flex items-center gap-4 p-4 rounded-xl border cursor-pointer transition',
                                            'border-indigo-500/60 bg-indigo-500/10' => $checked,
                                            'border-surface bg-surface-2 hover:border-zinc-600' =>
                                                !$checked && !$otherGroup,
                                            'border-surface bg-surface-2 opacity-50 cursor-not-allowed' => $otherGroup,
                                        ])>
                                            <input type="checkbox" wire:model="selectedCidIds"
                                                value="{{ $num->id }}" {{ $otherGroup ? 'disabled' : '' }}
                                                class="w-4 h-4 rounded border-surface text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <p class="text-sm font-semibold text-fg font-mono">
                                                        {{ $num->phone_number }}</p>
                                                    @if ($num->in_rotation)
                                                        <span
                                                            class="px-1.5 py-0.5 rounded text-[10px] bg-cyan-500/10 text-cyan-400 font-medium">In
                                                            Rotation</span>
                                                    @endif
                                                    @if (!$num->is_active)
                                                        <span
                                                            class="px-1.5 py-0.5 rounded text-[10px] bg-surface text-fg-muted">Inactive</span>
                                                    @endif
                                                </div>
                                                @if ($num->friendly_name)
                                                    <p class="text-xs text-fg-muted mt-0.5">{{ $num->friendly_name }}
                                                    </p>
                                                @endif
                                                @if ($otherGroup)
                                                    <p class="text-[11px] text-amber-400 mt-0.5">Assigned to another
                                                        group</p>
                                                @endif
                                            </div>
                                            @if ($num->twilio_sid)
                                                <span
                                                    class="text-[10px] text-fg-muted font-mono shrink-0">{{ Str::limit($num->twilio_sid, 20) }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>
