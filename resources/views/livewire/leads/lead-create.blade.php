<div class="min-h-screen bg-base flex flex-col">

    {{-- Top bar --}}
    <div class="flex items-center justify-between h-14 px-6 bg-surface border-b border-surface shrink-0">
        <div class="flex items-center gap-2">
            <a href="{{ route('leads.index') }}"
                class="p-1.5 rounded-lg text-fg-muted hover:text-fg hover:bg-hover transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <span class="text-xs text-fg-muted">Leads</span>
            <span class="text-fg-muted">/</span>
            <span class="text-sm font-semibold text-fg">New Lead</span>
        </div>
        @if ($campaign)
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-medium">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0-1.5 6M7 13l-1.5 6m0 0h9m-9 0a1.5 1.5 0 1 0 3 0m6 0a1.5 1.5 0 1 0 3 0" />
                </svg>
                {{ $campaign->name }}
            </span>
        @endif
    </div>

    {{-- Content --}}
    <div class="flex-1 flex items-start justify-center px-4 py-8">
        <div class="w-full max-w-lg space-y-5">

            @if (count($process['steps'] ?? []) === 0)
                <div class="bg-surface border border-surface rounded-xl p-6 text-center space-y-3">
                    <svg class="w-10 h-10 mx-auto text-fg-muted/30" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="font-semibold text-fg">No form configured</p>
                    <p class="text-xs text-fg-muted">This campaign has no lead form. Ask an admin to assign a lead
                        template to the campaign.</p>
                </div>
            @else
                {{-- Stepper indicator --}}
                @if ($processMode === 'stepper' && count($process['steps']) > 1)
                    <div class="bg-surface border border-surface rounded-xl px-5 py-4">
                        <div class="flex items-center gap-2">
                            @foreach ($process['steps'] as $si => $s)
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <div @class([
                                        'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 transition',
                                        'bg-indigo-600 text-white' => $si === $currentStep,
                                        'bg-green-600 text-white' => $si < $currentStep,
                                        'bg-surface-2 text-fg-muted' => $si > $currentStep,
                                    ])>
                                        @if ($si < $currentStep)
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @else
                                            {{ $si + 1 }}
                                        @endif
                                    </div>
                                    <span @class([
                                        'text-xs font-medium truncate transition',
                                        'text-fg' => $si === $currentStep,
                                        'text-green-400' => $si < $currentStep,
                                        'text-fg-muted' => $si > $currentStep,
                                    ])>
                                        {{ $s['title'] ?? 'Step ' . ($si + 1) }}
                                    </span>
                                </div>
                                @if (!$loop->last)
                                    <div class="w-6 h-px bg-surface-2 shrink-0"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="mt-3 w-full h-1 bg-surface-2 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-300"
                                style="width: {{ ($currentStep / max(count($process['steps']) - 1, 1)) * 100 }}%">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Form card --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-surface">
                        <p class="font-semibold text-fg">{{ $activeStep['title'] ?? 'Lead Details' }}</p>
                        <p class="text-xs text-fg-muted mt-0.5">Fill in the fields below and submit when ready.</p>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        @foreach ($activeStep['fields'] ?? [] as $field)
                            @php
                                $key = $field['key'];
                                $type = $field['type'] ?? 'text';
                                $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $key));
                                $placeholder = $field['placeholder'] ?? '';
                                $helpText = $field['help_text'] ?? '';
                                $isRequired = (bool) ($field['required'] ?? false);
                                $options = $field['options'] ?? [];
                                $hasError = $errors->has("formData.$key");
                                $inputClass =
                                    'w-full bg-surface-2 border rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-indigo-500 transition ' .
                                    ($hasError ? 'border-red-500' : 'border-surface');
                            @endphp

                            <div class="space-y-1.5">
                                @if ($type !== 'checkbox')
                                    <label class="block text-xs font-medium text-fg-muted">
                                        {{ $label }}
                                        @if ($isRequired)
                                            <span class="text-red-400">*</span>
                                        @endif
                                    </label>
                                @endif

                                @if ($type === 'textarea')
                                    <textarea wire:model.live="formData.{{ $key }}" rows="3" placeholder="{{ $placeholder }}"
                                        class="{{ $inputClass }} resize-none"></textarea>
                                @elseif ($type === 'select')
                                    <select wire:model.live="formData.{{ $key }}"
                                        class="{{ $inputClass }}">
                                        <option value="">Select {{ $label }}</option>
                                        @if ($key === 'store_id')
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach ($options as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @elseif ($type === 'checkbox')
                                    <label class="inline-flex items-center gap-3 cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" wire:model.live="formData.{{ $key }}"
                                                class="sr-only peer">
                                            <div
                                                class="w-9 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                                            </div>
                                        </div>
                                        <span class="text-sm text-fg">{{ $placeholder ?: $label }}</span>
                                    </label>
                                @else
                                    <input type="{{ $type === 'phone' ? 'tel' : $type }}"
                                        wire:model.live="formData.{{ $key }}"
                                        placeholder="{{ $placeholder }}" class="{{ $inputClass }}" />
                                @endif

                                @error("formData.$key")
                                    <p class="text-xs text-red-400">{{ $message }}</p>
                                @enderror

                                @if ($helpText)
                                    <p class="text-xs text-fg-muted">{{ $helpText }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between px-6 py-4 border-t border-surface bg-surface-2/40">
                        @if ($processMode === 'stepper' && $currentStep > 0)
                            <button type="button" wire:click="prevStep"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-surface-2 hover:bg-hover border border-surface rounded-lg text-sm text-fg transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                </svg>
                                Back
                            </button>
                        @else
                            <a href="{{ route('leads.index') }}"
                                class="text-sm text-fg-muted hover:text-fg transition">Cancel</a>
                        @endif

                        @if ($processMode === 'stepper' && $currentStep < count($process['steps']) - 1)
                            <button type="button" wire:click="nextStep"
                                class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-sm font-semibold text-white transition">
                                Next
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        @else
                            <button type="button" wire:click="save" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 rounded-lg text-sm font-semibold text-white transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Create Lead
                            </button>
                        @endif
                    </div>
                </div>

            @endif
        </div>
    </div>
</div>
