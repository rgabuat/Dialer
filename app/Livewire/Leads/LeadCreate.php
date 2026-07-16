<?php

namespace App\Livewire\Leads;

use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Store;
use Livewire\Component;

class LeadCreate extends Component
{
    public ?Campaign $campaign = null;
    public array $process = [];
    public string $processMode = 'single';
    public int $currentStep = 0;
    public array $formData = [];

    public function mount(): void
    {
        $campaignId = (int) session('active_campaign_id');
        $this->campaign = $campaignId ? Campaign::with('leadTemplate')->find($campaignId) : null;

        $effectiveProcess = ($this->campaign?->lead_template_id && $this->campaign?->leadTemplate)
            ? $this->campaign->leadTemplate->lead_process
            : $this->campaign?->lead_process;
        $this->process = $this->resolveProcess($effectiveProcess);
        $this->processMode = $this->process['mode'];

        foreach ($this->process['steps'] as $step) {
            foreach ($step['fields'] as $field) {
                $key = $field['key'];
                if (array_key_exists($key, $this->formData)) {
                    continue;
                }
                $this->formData[$key] = ($field['type'] ?? 'text') === 'checkbox' ? false : '';
            }
        }
    }

    public function nextStep(): void
    {
        if ($this->processMode !== 'stepper') {
            return;
        }

        $this->validate($this->rulesForStep($this->currentStep));

        if ($this->currentStep < count($this->process['steps']) - 1) {
            $this->currentStep++;
        }
    }

    public function prevStep(): void
    {
        if ($this->processMode !== 'stepper') {
            return;
        }

        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }

    public function save()
    {
        if (count($this->process['steps'] ?? []) === 0) {
            $this->addError('lead_process', 'No lead inputs configured for this campaign.');
            return;
        }

        $this->validate($this->rulesForAllSteps());

        $fillable = collect((new Lead())->getFillable())
            ->reject(fn (string $column) => in_array($column, ['created_by', 'last_actioned_by', 'dynamic_data'], true))
            ->values()
            ->all();

        $booleanColumns = ['admin_fee_credit', 'notify_sms', 'notify_email'];
        $arrayColumns = ['selected_units'];

        $leadPayload = [
            'created_by' => auth()->id(),
            'last_actioned_by' => auth()->id(),
            'campaign_id' => $this->campaign?->id,
            'dynamic_data' => $this->normalizedDynamicData(),
        ];

        foreach ($this->formData as $key => $value) {
            if (!in_array($key, $fillable, true)) {
                continue;
            }

            if (in_array($key, $booleanColumns, true)) {
                $leadPayload[$key] = (bool) $value;
                continue;
            }

            if (in_array($key, $arrayColumns, true)) {
                $leadPayload[$key] = is_array($value) ? $value : [];
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            $leadPayload[$key] = $value === '' ? null : $value;
        }

        $lead = Lead::create($leadPayload);

        $initialStage = $lead->pipeline_stage ?? 'interested';
        $stageLabels = [
            'interested'           => 'Interested',
            'converted'            => 'Converted',
            'expired'              => 'Expired',
            'no_longer_interested' => 'No Longer Interested',
        ];

        ActivityLog::create([
            'actor_type'   => 'App\\Models\\User',
            'actor_id'     => auth()->id(),
            'subject_type' => 'App\\Models\\Lead',
            'subject_id'   => $lead->id,
            'type'         => 'activity',
            'severity'     => 'info',
            'event'        => 'pipeline_stage_updated',
            'action'       => 'Set pipeline stage to ' . ($stageLabels[$initialStage] ?? ucfirst($initialStage)),
            'properties'   => ['from' => null, 'to' => $initialStage],
            'performed_at' => now(),
        ]);

        return redirect()->route('leads.index');
    }

    public function render()
    {
        $activeStep = $this->process['steps'][$this->currentStep] ?? ['title' => 'Lead Details', 'fields' => []];

        return view('livewire.leads.lead-create', [
            'stores' => Store::all(),
            'activeStep' => $activeStep,
        ])->layout('components.layouts.app');
    }

    private function rulesForStep(int $stepIndex): array
    {
        $rules = [];
        $step = $this->process['steps'][$stepIndex] ?? null;
        if (!$step) {
            return $rules;
        }

        foreach (($step['fields'] ?? []) as $field) {
            $key = $field['key'] ?? '';
            if ($key === '') {
                continue;
            }
            $rules["formData.$key"] = $this->fieldRules($field);
        }

        return $rules;
    }

    private function rulesForAllSteps(): array
    {
        $rules = [];
        foreach ($this->process['steps'] as $step) {
            foreach (($step['fields'] ?? []) as $field) {
                $key = $field['key'] ?? '';
                if ($key === '') {
                    continue;
                }
                $rules["formData.$key"] = $this->fieldRules($field);
            }
        }

        return $rules;
    }

    private function fieldRules(array $field): array
    {
        $rules = [];
        $type = $field['type'] ?? 'text';
        $required = (bool) ($field['required'] ?? false);

        $rules[] = $required ? 'required' : 'nullable';

        if (($field['key'] ?? '') === 'store_id') {
            $rules[] = 'integer';
            $rules[] = 'exists:stores,id';
            return $rules;
        }

        if ($type === 'email') {
            $rules[] = 'email';
            $rules[] = 'max:255';
        } elseif ($type === 'number') {
            $rules[] = 'numeric';
        } elseif ($type === 'date') {
            $rules[] = 'date';
        } elseif ($type === 'checkbox') {
            $rules[] = 'boolean';
        } elseif ($type === 'select') {
            $options = collect($field['options'] ?? [])->filter()->values()->all();
            if (count($options) > 0) {
                $rules[] = 'in:' . implode(',', array_map(fn ($v) => (string) $v, $options));
            }
        } else {
            $rules[] = 'string';
            $rules[] = 'max:1000';
        }

        return $rules;
    }

    private function resolveProcess(mixed $process): array
    {
        if (!is_array($process)) {
            return ['mode' => 'single', 'steps' => []];
        }

        $mode = in_array(($process['mode'] ?? null), ['single', 'stepper'], true)
            ? $process['mode']
            : 'single';

        $steps = [];
        foreach (($process['steps'] ?? []) as $rawStep) {
            if (!is_array($rawStep)) {
                continue;
            }

            $fields = [];
            foreach (($rawStep['fields'] ?? []) as $rawField) {
                if (!is_array($rawField)) {
                    continue;
                }

                $key = strtolower(trim((string) ($rawField['key'] ?? '')));
                if ($key === '') {
                    continue;
                }

                $fields[] = [
                    'key' => $key,
                    'label' => trim((string) ($rawField['label'] ?? $key)),
                    'type' => (string) ($rawField['type'] ?? 'text'),
                    'required' => (bool) ($rawField['required'] ?? false),
                    'placeholder' => (string) ($rawField['placeholder'] ?? ''),
                    'help_text' => (string) ($rawField['help_text'] ?? ''),
                    'options' => collect($rawField['options'] ?? [])->map(fn ($v) => trim((string) $v))->filter()->values()->all(),
                ];
            }

            if (count($fields) === 0) {
                continue;
            }

            $steps[] = [
                'title' => trim((string) ($rawStep['title'] ?? 'Step')) ?: 'Step',
                'fields' => $fields,
            ];
        }

        if ($mode === 'single') {
            $steps = count($steps) > 0 ? [array_values($steps)[0]] : [];
        }

        return [
            'mode' => $mode,
            'steps' => array_values($steps),
        ];
    }

    private function normalizedDynamicData(): array
    {
        $output = [];
        foreach ($this->formData as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            $output[$key] = $value === '' ? null : $value;
        }

        return $output;
    }
}
