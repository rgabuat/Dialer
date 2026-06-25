<div class="max-w-3xl mx-auto text-fg space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Create Lead</h1>
        <p class="text-sm text-fg-muted">
            @if ($campaign)
                Campaign: <span class="text-fg font-medium">{{ $campaign->name }}</span>
            @else
                No active campaign selected. A default lead form is shown.
            @endif
        </p>
    </div>

    <div class="bg-surface border border-surface rounded-lg p-6 space-y-5">
        @if (count($process['steps'] ?? []) === 0)
            <div class="bg-surface-2 border border-surface rounded-lg p-4 text-sm text-fg-muted">
                No lead inputs configured for this campaign. Ask admin to add inputs in Campaign Edit -> Lead Process.
            </div>
        @else
        @if ($processMode === 'stepper')
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-fg-muted">
                    <span>Step {{ $currentStep + 1 }} of {{ count($process['steps']) }}</span>
                    <span>{{ $activeStep['title'] ?? 'Step' }}</span>
                </div>
                <div class="w-full h-2 bg-surface-2 rounded-full overflow-hidden">
                    <div class="h-full bg-fuchsia-500" style="width: {{ ((int) $currentStep + 1) / max(count($process['steps']), 1) * 100 }}%"></div>
                </div>
            </div>
        @endif

        <div class="space-y-4">
            @foreach (($activeStep['fields'] ?? []) as $field)
                @php
                    $key = $field['key'];
                    $type = $field['type'] ?? 'text';
                    $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $key));
                    $placeholder = $field['placeholder'] ?? '';
                    $helpText = $field['help_text'] ?? '';
                    $isRequired = (bool) ($field['required'] ?? false);
                    $options = $field['options'] ?? [];
                @endphp

                <div>
                    <label class="text-sm text-fg-muted">
                        {{ $label }}
                        @if ($isRequired)
                            <span class="text-accent-red">*</span>
                        @endif
                    </label>

                    @if ($type === 'textarea')
                        <textarea wire:model.live="formData.{{ $key }}" rows="3" placeholder="{{ $placeholder }}"
                            class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md"></textarea>
                    @elseif ($type === 'select')
                        <select wire:model.live="formData.{{ $key }}"
                            class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md">
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
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-fg">
                            <input type="checkbox" wire:model.live="formData.{{ $key }}"
                                class="w-4 h-4 rounded text-fuchsia-600 border-surface-2 focus:ring-fuchsia-500 focus:ring-offset-0">
                            <span>{{ $placeholder ?: $label }}</span>
                        </label>
                    @else
                        <input
                            type="{{ $type === 'phone' ? 'tel' : $type }}"
                            wire:model.live="formData.{{ $key }}"
                            placeholder="{{ $placeholder }}"
                            class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
                    @endif

                    @error("formData.$key")
                        <span class="text-xs text-accent-red">{{ $message }}</span>
                    @enderror

                    @if ($helpText)
                        <p class="text-xs text-fg-muted mt-1">{{ $helpText }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex justify-between pt-2">
            <div>
                @if ($processMode === 'stepper' && $currentStep > 0)
                    <button type="button" wire:click="prevStep"
                        class="px-4 py-2 bg-surface-2 hover:bg-surface rounded-md text-sm text-fg">
                        Back
                    </button>
                @else
                    <a href="{{ route('leads.index') }}" class="text-sm text-fg-muted hover:text-fg">Back to Leads</a>
                @endif
            </div>

            <div>
                @if ($processMode === 'stepper' && $currentStep < count($process['steps']) - 1)
                    <button type="button" wire:click="nextStep"
                        class="px-4 py-2 bg-fuchsia-600 hover:bg-fuchsia-500 rounded-md text-sm text-white">
                        Next Step
                    </button>
                @else
                    <button type="button" wire:click="save"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-md text-sm text-white">
                        Create Lead
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
