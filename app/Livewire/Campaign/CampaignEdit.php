<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\InGroup;
use App\Models\User;
use App\Events\CampaignDeleted;
use Illuminate\Support\Str;

class CampaignEdit extends Component
{
    public Campaign $campaign;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public string $type = 'OUTBOUND';
    public string $dial_mode = 'MANUAL';
    public string $dial_level = '1.00';
    public string $caller_id = '';
    public bool $cid_rotation = false;
    public string $script = '';
    public int $acw_seconds = 0;
    public int $hopper_level = 50;
    public ?int $max_calls = null;

    /** @var array<int, string> IDs of in-groups currently assigned to this campaign */
    public array $selectedInGroupIds = [];

    public bool $confirmingDelete = false;

    // Voice & recording
    public string $tts_voice = 'alice';
    public string $tts_language = 'en-US';
    public string $tts_completed = 'Thank you for calling. Goodbye.';
    public string $tts_busy = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
    public string $tts_no_answer = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
    public string $tts_failed = 'We are sorry, we encountered an issue. Please call back later. Goodbye.';
    public string $tts_canceled = 'The call was ended. Thank you. Goodbye.';
    public string $greeting_message = '';
    public string $hold_music_url = '';
    public bool $recording_enabled = false;
    public string $recording_channels = 'both';
    public string $editor_tab = 'settings';

    // Lead process builder
    public string $lead_process_mode = 'single';
    public array $lead_process_steps = [];

    public function mount(Campaign $campaign): void
    {
        $this->campaign     = $campaign;
        $this->name         = $campaign->name;
        $this->description  = $campaign->description ?? '';
        $this->is_active    = $campaign->is_active;
        $this->type         = $campaign->type ?? 'OUTBOUND';
        $this->dial_mode    = $campaign->dial_mode ?? 'MANUAL';
        $this->dial_level   = (string) ($campaign->dial_level ?? '1.00');
        $this->caller_id    = $campaign->caller_id ?? '';
        $this->cid_rotation = (bool) ($campaign->cid_rotation ?? false);
        $this->script       = $campaign->script ?? '';
        $this->acw_seconds  = (int) ($campaign->acw_seconds ?? 0);
        $this->hopper_level = (int) ($campaign->hopper_level ?? 50);
        $this->max_calls    = $campaign->max_calls ? (int) $campaign->max_calls : null;

        // Voice & recording
        $this->tts_voice          = $campaign->tts_voice          ?? 'alice';
        $this->tts_language       = $campaign->tts_language       ?? 'en-US';
        $this->tts_completed      = $campaign->tts_completed      ?? 'Thank you for calling. Goodbye.';
        $this->tts_busy           = $campaign->tts_busy           ?? 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_no_answer      = $campaign->tts_no_answer      ?? 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_failed         = $campaign->tts_failed         ?? 'We are sorry, we encountered an issue. Please call back later. Goodbye.';
        $this->tts_canceled       = $campaign->tts_canceled       ?? 'The call was ended. Thank you. Goodbye.';
        $this->greeting_message   = $campaign->greeting_message   ?? '';
        $this->hold_music_url     = $campaign->hold_music_url     ?? '';
        $this->recording_enabled  = (bool) ($campaign->recording_enabled  ?? false);
        $this->recording_channels = $campaign->recording_channels ?? 'both';

        $normalizedLeadProcess = $this->normalizeLeadProcess($campaign->lead_process);
        $this->lead_process_mode = $normalizedLeadProcess['mode'];
        $this->lead_process_steps = $normalizedLeadProcess['steps'];

        // Load currently assigned in-groups (cast to string so wire:model checkboxes work)
        $this->selectedInGroupIds = $campaign->inGroups()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

    }

    public function save(): void
    {
        $this->validate([
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
            'type'         => ['required', 'in:OUTBOUND,INBOUND,BLENDED'],
            'dial_mode'    => ['required', 'in:MANUAL,PREVIEW,PROGRESSIVE,PREDICTIVE'],
            'dial_level'   => ['numeric', 'min:0.1', 'max:10'],
            'caller_id'    => ['nullable', 'string', 'max:50'],
            'cid_rotation' => ['boolean'],
            'script'       => ['nullable', 'string'],
            'acw_seconds'  => ['integer', 'min:0', 'max:3600'],
            'hopper_level' => ['integer', 'min:1', 'max:1000'],
            'max_calls'    => ['nullable', 'integer', 'min:1', 'max:100'],
            // Voice
            'tts_voice'          => ['required', 'in:alice,man,woman'],
            'tts_language'       => ['required', 'string', 'max:20'],
            'tts_completed'      => ['required', 'string', 'max:500'],
            'tts_busy'           => ['required', 'string', 'max:500'],
            'tts_no_answer'      => ['required', 'string', 'max:500'],
            'tts_failed'         => ['required', 'string', 'max:500'],
            'tts_canceled'       => ['required', 'string', 'max:500'],
            'greeting_message'   => ['nullable', 'string', 'max:500'],
            'hold_music_url'     => ['nullable', 'url', 'max:1000'],
            'recording_enabled'  => ['boolean'],
            'recording_channels' => ['required', 'in:both,inbound,outbound'],
            'lead_process_mode' => ['required', 'in:single,stepper'],
            'lead_process_steps' => ['nullable', 'array'],
            'lead_process_steps.*.title' => ['nullable', 'string', 'max:80'],
            'lead_process_steps.*.fields' => ['nullable', 'array'],
            'lead_process_steps.*.fields.*.key' => ['nullable', 'alpha_dash', 'max:50'],
            'lead_process_steps.*.fields.*.label' => ['nullable', 'string', 'max:80'],
            'lead_process_steps.*.fields.*.type' => ['nullable', 'in:text,textarea,email,phone,number,date,select,checkbox'],
            'lead_process_steps.*.fields.*.required' => ['boolean'],
            'lead_process_steps.*.fields.*.placeholder' => ['nullable', 'string', 'max:150'],
            'lead_process_steps.*.fields.*.help_text' => ['nullable', 'string', 'max:250'],
            'lead_process_steps.*.fields.*.options_text' => ['nullable', 'string', 'max:2000'],
            'selectedInGroupIds'   => ['array'],
            'selectedInGroupIds.*' => ['integer', 'exists:in_groups,id'],
        ]);

        $seenFieldKeys = [];
        $rawSteps = $this->lead_process_mode === 'single'
            ? array_slice($this->lead_process_steps, 0, 1)
            : $this->lead_process_steps;

        $steps = [];
        foreach ($rawSteps as $stepIndex => $step) {
            $title = trim((string) ($step['title'] ?? ''));
            $stepFields = [];

            foreach (($step['fields'] ?? []) as $fieldIndex => $field) {
                $key = strtolower(trim((string) ($field['key'] ?? '')));
                $label = trim((string) ($field['label'] ?? ''));
                $type = (string) ($field['type'] ?? 'text');

                if ($key === '' && $label === '') {
                    continue;
                }

                if ($key === '' || $label === '') {
                    $this->addError(
                        "lead_process_steps.{$stepIndex}.fields.{$fieldIndex}.key",
                        'Field key and label are required.'
                    );
                    continue;
                }

                if (isset($seenFieldKeys[$key])) {
                    $this->addError(
                        "lead_process_steps.{$stepIndex}.fields.{$fieldIndex}.key",
                        'Field keys must be unique across the full lead process.'
                    );
                    continue;
                }

                $seenFieldKeys[$key] = true;

                $options = [];
                if ($type === 'select') {
                    $options = collect(explode("\n", (string) ($field['options_text'] ?? '')))
                        ->map(fn (string $item) => trim($item))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }

                $stepFields[] = [
                    'uid' => (string) ($field['uid'] ?? Str::uuid()),
                    'key' => $key,
                    'label' => $label,
                    'type' => $type,
                    'required' => (bool) ($field['required'] ?? false),
                    'placeholder' => trim((string) ($field['placeholder'] ?? '')) ?: null,
                    'help_text' => trim((string) ($field['help_text'] ?? '')) ?: null,
                    'options' => $options,
                ];
            }

            if (count($stepFields) > 0) {
                $steps[] = [
                    'title' => $title !== '' ? $title : ('Step ' . (count($steps) + 1)),
                    'fields' => $stepFields,
                ];
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $leadProcess = [
            'mode' => $this->lead_process_mode,
            'steps' => $steps,
        ];

        $this->campaign->update([
            'name'         => $this->name,
            'description'  => $this->description ?: null,
            'is_active'    => $this->is_active,
            'type'         => $this->type,
            'dial_mode'    => $this->dial_mode,
            'dial_level'   => $this->dial_level,
            'caller_id'    => $this->caller_id ?: null,
            'cid_rotation' => $this->cid_rotation,
            'script'       => $this->script ?: null,
            'acw_seconds'  => $this->acw_seconds,
            'hopper_level' => $this->hopper_level,
            'max_calls'    => $this->max_calls,
            'tts_voice'          => $this->tts_voice,
            'tts_language'       => $this->tts_language,
            'tts_completed'      => $this->tts_completed,
            'tts_busy'           => $this->tts_busy,
            'tts_no_answer'      => $this->tts_no_answer,
            'tts_failed'         => $this->tts_failed,
            'tts_canceled'       => $this->tts_canceled,
            'greeting_message'   => $this->greeting_message ?: null,
            'hold_music_url'     => $this->hold_music_url ?: null,
            'recording_enabled'  => $this->recording_enabled,
            'recording_channels' => $this->recording_channels,
            'lead_process'       => count($steps) > 0 ? $leadProcess : null,
        ]);

        // Sync in-group assignments via campaign_id FK
        $selectedIds = array_map('intval', $this->selectedInGroupIds);
        InGroup::whereIn('id', $selectedIds)
            ->update(['campaign_id' => $this->campaign->id]);
        InGroup::where('campaign_id', $this->campaign->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['campaign_id' => null]);

        session()->flash('success', 'Campaign updated successfully.');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        $campaignId = $this->campaign->id;

        // Collect all users in the campaign's user groups before deleting
        $groupIds = $this->campaign->userGroups()->pluck('id');
        $userIds  = User::whereIn('user_group_id', $groupIds)->pluck('id');

        $this->campaign->delete();

        // Notify each affected user — they will be logged out if this was their active campaign
        foreach ($userIds as $userId) {
            broadcast(new CampaignDeleted($userId, $campaignId));
        }

        $this->redirect(route('campaigns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.campaign.campaign-edit', [
            'allInGroups' => InGroup::orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }

    public function addLeadStep(): void
    {
        $this->lead_process_steps[] = [
            'title' => 'Step ' . (count($this->lead_process_steps) + 1),
            'fields' => [
                $this->newLeadField(),
            ],
        ];
    }

    public function removeLeadStep(int $stepIndex): void
    {
        unset($this->lead_process_steps[$stepIndex]);
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function addLeadField(int $stepIndex): void
    {
        if (!isset($this->lead_process_steps[$stepIndex])) {
            return;
        }

        $this->lead_process_steps[$stepIndex]['fields'][] = $this->newLeadField();
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

        $fields = $this->lead_process_steps[$stepIndex]['fields'] ?? [];
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

    public function moveLeadStepUp(int $stepIndex): void
    {
        if ($stepIndex <= 0 || !isset($this->lead_process_steps[$stepIndex])) {
            return;
        }

        [$this->lead_process_steps[$stepIndex - 1], $this->lead_process_steps[$stepIndex]] =
            [$this->lead_process_steps[$stepIndex], $this->lead_process_steps[$stepIndex - 1]];
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function moveLeadStepDown(int $stepIndex): void
    {
        if (!isset($this->lead_process_steps[$stepIndex + 1])) {
            return;
        }

        [$this->lead_process_steps[$stepIndex + 1], $this->lead_process_steps[$stepIndex]] =
            [$this->lead_process_steps[$stepIndex], $this->lead_process_steps[$stepIndex + 1]];
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function moveLeadFieldUp(int $stepIndex, int $fieldIndex): void
    {
        if ($fieldIndex <= 0 || !isset($this->lead_process_steps[$stepIndex]['fields'][$fieldIndex])) {
            return;
        }

        [$this->lead_process_steps[$stepIndex]['fields'][$fieldIndex - 1], $this->lead_process_steps[$stepIndex]['fields'][$fieldIndex]] =
            [$this->lead_process_steps[$stepIndex]['fields'][$fieldIndex], $this->lead_process_steps[$stepIndex]['fields'][$fieldIndex - 1]];
        $this->lead_process_steps[$stepIndex]['fields'] = array_values($this->lead_process_steps[$stepIndex]['fields']);
    }

    public function moveLeadFieldDown(int $stepIndex, int $fieldIndex): void
    {
        if (!isset($this->lead_process_steps[$stepIndex]['fields'][$fieldIndex + 1])) {
            return;
        }

        [$this->lead_process_steps[$stepIndex]['fields'][$fieldIndex + 1], $this->lead_process_steps[$stepIndex]['fields'][$fieldIndex]] =
            [$this->lead_process_steps[$stepIndex]['fields'][$fieldIndex], $this->lead_process_steps[$stepIndex]['fields'][$fieldIndex + 1]];
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

        $fields = $this->lead_process_steps[$stepIndex]['fields'];
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
        $toFields = $this->lead_process_steps[$toStepIndex]['fields'];

        $moved = $fromFields[$fromIndex];
        array_splice($fromFields, $fromIndex, 1);

        if (count($fromFields) === 0) {
            $fromFields[] = $this->newLeadField();
        }

        $toIndex = max(0, min($toIndex, count($toFields)));
        array_splice($toFields, $toIndex, 0, [$moved]);

        $this->lead_process_steps[$fromStepIndex]['fields'] = array_values($fromFields);
        $this->lead_process_steps[$toStepIndex]['fields'] = array_values($toFields);
    }

    private function normalizeLeadProcess(mixed $process): array
    {
        if (!is_array($process)) {
            return [
                'mode' => 'single',
                'steps' => [],
            ];
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

                $optionsText = collect($rawField['options'] ?? [])
                    ->map(fn ($item) => trim((string) $item))
                    ->filter()
                    ->implode("\n");

                $fields[] = [
                    'uid' => (string) ($rawField['uid'] ?? Str::uuid()),
                    'key' => strtolower(trim((string) ($rawField['key'] ?? ''))),
                    'label' => trim((string) ($rawField['label'] ?? '')),
                    'type' => (string) ($rawField['type'] ?? 'text'),
                    'required' => (bool) ($rawField['required'] ?? false),
                    'placeholder' => (string) ($rawField['placeholder'] ?? ''),
                    'help_text' => (string) ($rawField['help_text'] ?? ''),
                    'options_text' => $optionsText,
                ];
            }

            if (count($fields) === 0) {
                $fields[] = $this->newLeadField();
            }

            $steps[] = [
                'title' => trim((string) ($rawStep['title'] ?? 'Step')) ?: 'Step',
                'fields' => $fields,
            ];
        }

        return [
            'mode' => $mode,
            'steps' => $steps,
        ];
    }

    private function newLeadField(string $type = 'text'): array
    {
        $allowedTypes = ['text', 'textarea', 'email', 'phone', 'number', 'date', 'select', 'checkbox'];
        $resolvedType = in_array($type, $allowedTypes, true) ? $type : 'text';

        return [
            'uid' => (string) Str::uuid(),
            'key' => '',
            'label' => '',
            'type' => $resolvedType,
            'required' => false,
            'placeholder' => '',
            'help_text' => '',
            'options_text' => '',
        ];
    }
}
