<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\InGroup;
use Illuminate\Support\Str;
use Livewire\Component;

class AdminCampaignCreate extends Component
{
    // ── Stepper ──────────────────────────────────────────────────────────────
    public int $step = 1;

    // ── Step 1: Details ──────────────────────────────────────────────────────
    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;
    public string $type        = 'OUTBOUND';
    public string $script      = '';

    // ── Step 2: Lead Form ────────────────────────────────────────────────────
    public string $lead_process_mode  = 'single';
    public array  $lead_process_steps = [];

    // ── Step 3: Call Settings ────────────────────────────────────────────────
    public string  $dial_mode   = 'MANUAL';
    public string  $dial_level  = '1.00';
    public string  $caller_id   = '';
    public int     $acw_seconds  = 0;
    public int     $hopper_level = 50;
    public ?int    $max_calls    = null;
    public array   $selectedInGroupIds = [];

    // Voice & TTS
    public string $tts_voice        = 'alice';
    public string $tts_language     = 'en-US';
    public string $greeting_message = '';
    public string $hold_music_url   = '';
    public string $tts_completed    = 'Thank you for calling. Goodbye.';
    public string $tts_busy         = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
    public string $tts_no_answer    = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
    public string $tts_failed       = 'We are sorry, we encountered an issue. Please call back later. Goodbye.';
    public string $tts_canceled     = 'The call was ended. Thank you. Goodbye.';

    // Recording
    public bool   $recording_enabled  = false;
    public string $recording_channels = 'both';

    // ── Step navigation ───────────────────────────────────────────────────────
    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateOnly('name', ['name' => ['required', 'string', 'max:255']]);
        }

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    // ── Lead Process Builder methods (mirrored from CampaignEdit) ─────────────
    public function addLeadStep(): void
    {
        $this->lead_process_steps[] = [
            'title'  => 'Step ' . (count($this->lead_process_steps) + 1),
            'fields' => [$this->newLeadField()],
        ];
    }

    public function removeLeadStep(int $stepIndex): void
    {
        unset($this->lead_process_steps[$stepIndex]);
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function addLeadFieldOfType(int $stepIndex, string $type): void
    {
        if (!isset($this->lead_process_steps[$stepIndex])) {
            return;
        }
        $this->lead_process_steps[$stepIndex]['fields'][] = $this->newLeadField($type);
    }

    public function insertLeadFieldAt(int $stepIndex, int $fieldIndex, string $type): void
    {
        if (!isset($this->lead_process_steps[$stepIndex])) {
            return;
        }
        $fields     = $this->lead_process_steps[$stepIndex]['fields'] ?? [];
        $fieldIndex = max(0, min($fieldIndex, count($fields)));
        array_splice($fields, $fieldIndex, 0, [$this->newLeadField($type)]);
        $this->lead_process_steps[$stepIndex]['fields'] = array_values($fields);
    }

    public function removeLeadField(int $stepIndex, int $fieldIndex): void
    {
        if (!isset($this->lead_process_steps[$stepIndex]['fields'][$fieldIndex])) {
            return;
        }
        unset($this->lead_process_steps[$stepIndex]['fields'][$fieldIndex]);
        $this->lead_process_steps[$stepIndex]['fields'] = array_values($this->lead_process_steps[$stepIndex]['fields']);
    }

    public function moveLeadStepTo(int $fromIndex, int $toIndex): void
    {
        if (!isset($this->lead_process_steps[$fromIndex])) {
            return;
        }
        $toIndex = max(0, min($toIndex, count($this->lead_process_steps) - 1));
        if ($fromIndex === $toIndex) {
            return;
        }
        $moved = $this->lead_process_steps[$fromIndex];
        array_splice($this->lead_process_steps, $fromIndex, 1);
        array_splice($this->lead_process_steps, $toIndex, 0, [$moved]);
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function moveLeadFieldTo(int $stepIndex, int $fromIndex, int $toIndex): void
    {
        if (!isset($this->lead_process_steps[$stepIndex]['fields'][$fromIndex])) {
            return;
        }
        $fields  = $this->lead_process_steps[$stepIndex]['fields'];
        $toIndex = max(0, min($toIndex, count($fields) - 1));
        if ($fromIndex === $toIndex) {
            return;
        }
        $moved = $fields[$fromIndex];
        array_splice($fields, $fromIndex, 1);
        array_splice($fields, $toIndex, 0, [$moved]);
        $this->lead_process_steps[$stepIndex]['fields'] = array_values($fields);
    }

    public function moveLeadFieldAcrossSteps(int $fromStepIndex, int $fromIndex, int $toStepIndex, int $toIndex): void
    {
        if (!isset($this->lead_process_steps[$fromStepIndex]['fields'][$fromIndex])) {
            return;
        }
        if (!isset($this->lead_process_steps[$toStepIndex]['fields'])) {
            return;
        }
        $fromFields = $this->lead_process_steps[$fromStepIndex]['fields'];
        $toFields   = $this->lead_process_steps[$toStepIndex]['fields'];

        $moved = $fromFields[$fromIndex];
        array_splice($fromFields, $fromIndex, 1);
        if (count($fromFields) === 0) {
            $fromFields[] = $this->newLeadField();
        }
        $toIndex = max(0, min($toIndex, count($toFields)));
        array_splice($toFields, $toIndex, 0, [$moved]);

        $this->lead_process_steps[$fromStepIndex]['fields'] = array_values($fromFields);
        $this->lead_process_steps[$toStepIndex]['fields']   = array_values($toFields);
    }

    private function newLeadField(string $type = 'text'): array
    {
        $allowed      = ['text', 'textarea', 'email', 'phone', 'number', 'date', 'select', 'checkbox'];
        $resolvedType = in_array($type, $allowed, true) ? $type : 'text';

        return [
            'uid'          => (string) Str::uuid(),
            'key'          => '',
            'label'        => '',
            'type'         => $resolvedType,
            'required'     => false,
            'placeholder'  => '',
            'help_text'    => '',
            'options_text' => '',
        ];
    }

    // ── Save ─────────────────────────────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'is_active'          => ['boolean'],
            'type'               => ['required', 'in:' . implode(',', Campaign::TYPES)],
            'script'             => ['nullable', 'string'],
            'lead_process_mode'  => ['required', 'in:single,stepper'],
            'lead_process_steps' => ['nullable', 'array'],
            'lead_process_steps.*.title'                    => ['nullable', 'string', 'max:80'],
            'lead_process_steps.*.fields'                   => ['nullable', 'array'],
            'lead_process_steps.*.fields.*.key'             => ['nullable', 'alpha_dash', 'max:50'],
            'lead_process_steps.*.fields.*.label'           => ['nullable', 'string', 'max:80'],
            'lead_process_steps.*.fields.*.type'            => ['nullable', 'in:text,textarea,email,phone,number,date,select,checkbox'],
            'lead_process_steps.*.fields.*.required'        => ['boolean'],
            'lead_process_steps.*.fields.*.placeholder'     => ['nullable', 'string', 'max:150'],
            'lead_process_steps.*.fields.*.help_text'       => ['nullable', 'string', 'max:250'],
            'lead_process_steps.*.fields.*.options_text'    => ['nullable', 'string', 'max:2000'],
            'dial_mode'          => ['required', 'in:' . implode(',', Campaign::DIAL_MODES)],
            'dial_level'         => ['numeric', 'min:0.1', 'max:10'],
            'caller_id'          => ['nullable', 'string', 'max:50'],
            'acw_seconds'        => ['integer', 'min:0', 'max:3600'],
            'hopper_level'       => ['integer', 'min:1', 'max:1000'],
            'max_calls'          => ['nullable', 'integer', 'min:1', 'max:100'],
            'selectedInGroupIds'   => ['array'],
            'selectedInGroupIds.*' => ['integer', 'exists:in_groups,id'],
            'tts_voice'          => ['required', 'in:' . implode(',', Campaign::TTS_VOICES)],
            'tts_language'       => ['required', 'string', 'max:20'],
            'greeting_message'   => ['nullable', 'string', 'max:500'],
            'hold_music_url'     => ['nullable', 'url', 'max:1000'],
            'tts_completed'      => ['required', 'string', 'max:500'],
            'tts_busy'           => ['required', 'string', 'max:500'],
            'tts_no_answer'      => ['required', 'string', 'max:500'],
            'tts_failed'         => ['required', 'string', 'max:500'],
            'tts_canceled'       => ['required', 'string', 'max:500'],
            'recording_enabled'  => ['boolean'],
            'recording_channels' => ['required', 'in:' . implode(',', Campaign::REC_CHANNELS)],
        ]);

        // Build lead process
        $seenKeys = [];
        $rawSteps = $this->lead_process_mode === 'single'
            ? array_slice($this->lead_process_steps, 0, 1)
            : $this->lead_process_steps;

        $steps = [];
        foreach ($rawSteps as $si => $step) {
            $stepFields = [];
            foreach (($step['fields'] ?? []) as $fi => $field) {
                $key   = strtolower(trim((string) ($field['key'] ?? '')));
                $label = trim((string) ($field['label'] ?? ''));
                $type  = (string) ($field['type'] ?? 'text');

                if ($key === '' && $label === '') {
                    continue;
                }
                if ($key === '' || $label === '') {
                    $this->addError("lead_process_steps.{$si}.fields.{$fi}.key", 'Field key and label are required.');
                    continue;
                }
                if (isset($seenKeys[$key])) {
                    $this->addError("lead_process_steps.{$si}.fields.{$fi}.key", 'Field keys must be unique.');
                    continue;
                }
                $seenKeys[$key] = true;

                $options = [];
                if ($type === 'select') {
                    $options = collect(explode("\n", (string) ($field['options_text'] ?? '')))
                        ->map(fn (string $i) => trim($i))
                        ->filter()->unique()->values()->all();
                }

                $stepFields[] = [
                    'uid'         => (string) ($field['uid'] ?? Str::uuid()),
                    'key'         => $key,
                    'label'       => $label,
                    'type'        => $type,
                    'required'    => (bool) ($field['required'] ?? false),
                    'placeholder' => trim((string) ($field['placeholder'] ?? '')) ?: null,
                    'help_text'   => trim((string) ($field['help_text'] ?? '')) ?: null,
                    'options'     => $options,
                ];
            }

            if (count($stepFields) > 0) {
                $title    = trim((string) ($step['title'] ?? ''));
                $steps[]  = [
                    'title'  => $title !== '' ? $title : ('Step ' . (count($steps) + 1)),
                    'fields' => $stepFields,
                ];
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $campaign = Campaign::create([
            'name'               => $this->name,
            'description'        => $this->description ?: null,
            'is_active'          => $this->is_active,
            'type'               => $this->type,
            'script'             => $this->script ?: null,
            'dial_mode'          => $this->dial_mode,
            'dial_level'         => $this->dial_level,
            'caller_id'          => $this->caller_id ?: null,
            'acw_seconds'        => $this->acw_seconds,
            'hopper_level'       => $this->hopper_level,
            'max_calls'          => $this->max_calls,
            'tts_voice'          => $this->tts_voice,
            'tts_language'       => $this->tts_language,
            'greeting_message'   => $this->greeting_message ?: null,
            'hold_music_url'     => $this->hold_music_url ?: null,
            'tts_completed'      => $this->tts_completed,
            'tts_busy'           => $this->tts_busy,
            'tts_no_answer'      => $this->tts_no_answer,
            'tts_failed'         => $this->tts_failed,
            'tts_canceled'       => $this->tts_canceled,
            'recording_enabled'  => $this->recording_enabled,
            'recording_channels' => $this->recording_channels,
            'lead_process'       => count($steps) > 0 ? ['mode' => $this->lead_process_mode, 'steps' => $steps] : null,
        ]);

        // Assign in-groups
        $selectedIds = array_map('intval', $this->selectedInGroupIds);
        InGroup::whereIn('id', $selectedIds)->update(['campaign_id' => $campaign->id]);

        session()->flash('success', 'Campaign created successfully.');
        $this->redirect(route('admin.campaigns.index'));
    }

    public function render()
    {
        return view('livewire.admin.campaign-create', [
            'allInGroups' => InGroup::orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['heading' => 'New Campaign']);
    }
}
