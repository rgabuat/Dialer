<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\CidGroup;
use App\Models\InGroup;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class CampaignModal extends Component
{
    // ── UI state ─────────────────────────────────────────────────────────────
    public bool   $open = false;
    public string $mode = 'create'; // 'create' | 'edit'
    public int    $step = 1;
    public bool   $saving = false;
    public ?int   $campaignId = null;

    // ── Step 1: Details ──────────────────────────────────────────────────────
    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;
    public string $type        = 'OUTBOUND';
    public string $script      = '';

    // ── Step 2: Dialer ───────────────────────────────────────────────────────
    public string $dial_mode    = 'MANUAL';
    public string $dial_level   = '1.00';
    public int    $acw_seconds  = 0;
    public int    $hopper_level = 50;
    public ?int   $max_calls    = null;

    // ── Step 3: Lead Form ────────────────────────────────────────────────────
    public string $lead_process_mode  = 'single';
    public array  $lead_process_steps = [];

    // ── Step 4: Inbound Groups ───────────────────────────────────────────────
    public array $selectedInGroupIds = [];

    // ── Step 5: CID & Caller ID ──────────────────────────────────────────────
    public string  $caller_id   = '';
    public bool    $cid_rotation = false;
    public ?int    $cid_group_id = null;

    // ── Step 6: Voice & Recording ────────────────────────────────────────────
    public string $tts_voice         = 'alice';
    public string $tts_language      = 'en-US';
    public string $greeting_message  = '';
    public string $hold_music_url    = '';
    public string $tts_completed     = 'Thank you for calling. Goodbye.';
    public string $tts_busy          = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
    public string $tts_no_answer     = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
    public string $tts_failed        = 'We are sorry, we encountered an issue. Please call back later. Goodbye.';
    public string $tts_canceled      = 'The call was ended. Thank you. Goodbye.';
    public bool   $recording_enabled  = false;
    public string $recording_channels = 'both';

    // ── Event listeners ──────────────────────────────────────────────────────

    #[On('open-create')]
    public function openCreate(): void
    {
        $this->reset([
            'campaignId', 'name', 'description', 'type', 'script',
            'dial_mode', 'dial_level', 'acw_seconds', 'hopper_level', 'max_calls',
            'lead_process_mode', 'lead_process_steps', 'selectedInGroupIds',
            'caller_id', 'cid_rotation', 'cid_group_id',
            'tts_voice', 'tts_language', 'greeting_message', 'hold_music_url',
            'tts_completed', 'tts_busy', 'tts_no_answer', 'tts_failed', 'tts_canceled',
            'recording_enabled', 'recording_channels',
        ]);
        $this->resetErrorBag();

        $this->is_active          = true;
        $this->dial_mode          = 'MANUAL';
        $this->dial_level         = '1.00';
        $this->hopper_level       = 50;
        $this->type               = 'OUTBOUND';
        $this->lead_process_mode  = 'single';
        $this->tts_voice          = 'alice';
        $this->tts_language       = 'en-US';
        $this->recording_channels = 'both';
        $this->tts_completed      = 'Thank you for calling. Goodbye.';
        $this->tts_busy           = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_no_answer      = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_failed         = 'We are sorry, we encountered an issue. Please call back later. Goodbye.';
        $this->tts_canceled       = 'The call was ended. Thank you. Goodbye.';

        $this->mode  = 'create';
        $this->step  = 1;
        $this->open  = true;
    }

    #[On('open-edit')]
    public function openEdit(int $id): void
    {
        $campaign = Campaign::findOrFail($id);

        $this->campaignId    = $campaign->id;
        $this->name          = $campaign->name;
        $this->description   = $campaign->description ?? '';
        $this->is_active     = (bool) $campaign->is_active;
        $this->type          = $campaign->type ?? 'OUTBOUND';
        $this->script        = $campaign->script ?? '';

        $this->dial_mode     = $campaign->dial_mode ?? 'MANUAL';
        $this->dial_level    = (string) ($campaign->dial_level ?? '1.00');
        $this->acw_seconds   = (int) ($campaign->acw_seconds ?? 0);
        $this->hopper_level  = (int) ($campaign->hopper_level ?? 50);
        $this->max_calls     = $campaign->max_calls ? (int) $campaign->max_calls : null;

        $normalized = $this->normalizeLeadProcess($campaign->lead_process);
        $this->lead_process_mode  = $normalized['mode'];
        $this->lead_process_steps = $normalized['steps'];

        $this->selectedInGroupIds = $campaign->inGroups()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $this->caller_id    = $campaign->caller_id ?? '';
        $this->cid_rotation  = (bool) ($campaign->cid_rotation ?? false);
        $this->cid_group_id  = $campaign->cid_group_id ? (int) $campaign->cid_group_id : null;

        $this->tts_voice          = $campaign->tts_voice          ?? 'alice';
        $this->tts_language       = $campaign->tts_language       ?? 'en-US';
        $this->greeting_message   = $campaign->greeting_message   ?? '';
        $this->hold_music_url     = $campaign->hold_music_url     ?? '';
        $this->tts_completed      = $campaign->tts_completed      ?? 'Thank you for calling. Goodbye.';
        $this->tts_busy           = $campaign->tts_busy           ?? 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_no_answer      = $campaign->tts_no_answer      ?? 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_failed         = $campaign->tts_failed         ?? 'We are sorry, we encountered an issue. Please call back later. Goodbye.';
        $this->tts_canceled       = $campaign->tts_canceled       ?? 'The call was ended. Thank you. Goodbye.';
        $this->recording_enabled  = (bool) ($campaign->recording_enabled ?? false);
        $this->recording_channels = $campaign->recording_channels ?? 'both';

        $this->resetErrorBag();
        $this->mode = 'edit';
        $this->step = 1;
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    // ── Stepper ───────────────────────────────────────────────────────────────

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateOnly('name', ['name' => 'required|string|max:255']);
            if ($this->getErrorBag()->has('name')) return;
        }
        if ($this->step < 4) $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function goToStep(int $s): void
    {
        if ($s >= 1 && $s <= 4) $this->step = $s;
    }

    // ── Save ─────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'is_active'    => 'boolean',
            'type'         => ['required', 'in:' . implode(',', Campaign::TYPES)],
            'script'       => 'nullable|string',
            'dial_mode'    => ['required', 'in:' . implode(',', Campaign::DIAL_MODES)],
            'dial_level'   => 'numeric|min:0.1|max:10',
            'acw_seconds'  => 'integer|min:0|max:3600',
            'hopper_level' => 'integer|min:1|max:1000',
            'max_calls'    => 'nullable|integer|min:1|max:999',
            'lead_process_mode'                          => 'required|in:single,stepper',
            'lead_process_steps'                         => 'nullable|array',
            'lead_process_steps.*.title'                 => 'nullable|string|max:80',
            'lead_process_steps.*.fields'                => 'nullable|array',
            'lead_process_steps.*.fields.*.key'          => 'nullable|alpha_dash|max:50',
            'lead_process_steps.*.fields.*.label'        => 'nullable|string|max:80',
            'lead_process_steps.*.fields.*.type'         => 'nullable|in:text,textarea,email,phone,number,date,select,checkbox',
            'lead_process_steps.*.fields.*.required'     => 'boolean',
            'lead_process_steps.*.fields.*.placeholder'  => 'nullable|string|max:150',
            'lead_process_steps.*.fields.*.help_text'    => 'nullable|string|max:250',
            'lead_process_steps.*.fields.*.options_text' => 'nullable|string|max:2000',
            'selectedInGroupIds'                         => 'array',
            'selectedInGroupIds.*'                       => 'integer|exists:in_groups,id',
            'caller_id'          => 'nullable|string|max:50',
            'cid_rotation'       => 'boolean',
            'cid_group_id'       => 'nullable|integer|exists:cid_groups,id',
            'tts_voice'          => ['required', 'in:' . implode(',', Campaign::TTS_VOICES)],
            'tts_language'       => 'required|string|max:20',
            'greeting_message'   => 'nullable|string|max:500',
            'hold_music_url'     => 'nullable|url|max:1000',
            'tts_completed'      => 'required|string|max:500',
            'tts_busy'           => 'required|string|max:500',
            'tts_no_answer'      => 'required|string|max:500',
            'tts_failed'         => 'required|string|max:500',
            'tts_canceled'       => 'required|string|max:500',
            'recording_enabled'  => 'boolean',
            'recording_channels' => ['required', 'in:' . implode(',', Campaign::REC_CHANNELS)],
        ]);

        // Build lead process JSON
        $leadProcess = $this->buildLeadProcess();

        $payload = [
            'name'               => $this->name,
            'description'        => $this->description ?: null,
            'is_active'          => $this->is_active,
            'type'               => $this->type,
            'script'             => $this->script ?: null,
            'dial_mode'          => $this->dial_mode,
            'dial_level'         => $this->dial_level,
            'acw_seconds'        => $this->acw_seconds,
            'hopper_level'       => $this->hopper_level,
            'max_calls'          => $this->max_calls,
            'lead_process'       => $leadProcess,
            'caller_id'          => $this->caller_id ?: null,
            'cid_rotation'       => $this->cid_rotation,
            'cid_group_id'       => $this->cid_group_id,
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
        ];

        if ($this->mode === 'create') {
            $campaign = Campaign::create($payload);
        } else {
            $campaign = Campaign::findOrFail($this->campaignId);
            $campaign->update($payload);
        }

        // Sync inbound groups
        $ids = array_map('intval', $this->selectedInGroupIds);
        InGroup::whereIn('id', $ids)->update(['campaign_id' => $campaign->id]);
        InGroup::where('campaign_id', $campaign->id)->whereNotIn('id', $ids)->update(['campaign_id' => null]);

        $this->open = false;
        session()->flash('success', $this->mode === 'create' ? 'Campaign created.' : 'Campaign updated.');
        $this->dispatch('campaign-saved');
    }

    // ── Lead process builder ──────────────────────────────────────────────────

    public function addLeadStep(): void
    {
        $this->lead_process_steps[] = [
            'title'  => 'Step ' . (count($this->lead_process_steps) + 1),
            'fields' => [$this->newLeadField()],
        ];
    }

    public function removeLeadStep(int $si): void
    {
        unset($this->lead_process_steps[$si]);
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function addLeadFieldOfType(int $si, string $type): void
    {
        if (!isset($this->lead_process_steps[$si])) return;
        $this->lead_process_steps[$si]['fields'][] = $this->newLeadField($type);
    }

    public function insertLeadFieldAt(int $si, int $fi, string $type): void
    {
        if (!isset($this->lead_process_steps[$si])) return;
        $fields = $this->lead_process_steps[$si]['fields'] ?? [];
        $fi     = max(0, min($fi, count($fields)));
        array_splice($fields, $fi, 0, [$this->newLeadField($type)]);
        $this->lead_process_steps[$si]['fields'] = array_values($fields);
    }

    public function removeLeadField(int $si, int $fi): void
    {
        if (!isset($this->lead_process_steps[$si]['fields'][$fi])) return;
        unset($this->lead_process_steps[$si]['fields'][$fi]);
        $this->lead_process_steps[$si]['fields'] = array_values($this->lead_process_steps[$si]['fields']);
    }

    public function moveLeadStepTo(int $from, int $to): void
    {
        if (!isset($this->lead_process_steps[$from])) return;
        $to    = max(0, min($to, count($this->lead_process_steps) - 1));
        $moved = $this->lead_process_steps[$from];
        array_splice($this->lead_process_steps, $from, 1);
        array_splice($this->lead_process_steps, $to, 0, [$moved]);
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function moveLeadFieldTo(int $si, int $from, int $to): void
    {
        if (!isset($this->lead_process_steps[$si]['fields'][$from])) return;
        $fields = $this->lead_process_steps[$si]['fields'];
        $to     = max(0, min($to, count($fields) - 1));
        if ($from === $to) return;
        $moved  = $fields[$from];
        array_splice($fields, $from, 1);
        array_splice($fields, $to, 0, [$moved]);
        $this->lead_process_steps[$si]['fields'] = array_values($fields);
    }

    public function moveLeadFieldAcrossSteps(int $fromSi, int $fromFi, int $toSi, int $toFi): void
    {
        if (!isset($this->lead_process_steps[$fromSi]['fields'][$fromFi])) return;
        if (!isset($this->lead_process_steps[$toSi]['fields'])) return;
        $fromFields = $this->lead_process_steps[$fromSi]['fields'];
        $toFields   = $this->lead_process_steps[$toSi]['fields'];
        $moved      = $fromFields[$fromFi];
        array_splice($fromFields, $fromFi, 1);
        if (count($fromFields) === 0) $fromFields[] = $this->newLeadField();
        $toFi = max(0, min($toFi, count($toFields)));
        array_splice($toFields, $toFi, 0, [$moved]);
        $this->lead_process_steps[$fromSi]['fields'] = array_values($fromFields);
        $this->lead_process_steps[$toSi]['fields']   = array_values($toFields);
    }

    private function newLeadField(string $type = 'text'): array
    {
        $allowed = ['text', 'textarea', 'email', 'phone', 'number', 'date', 'select', 'checkbox'];
        return [
            'uid'          => (string) Str::uuid(),
            'key'          => '',
            'label'        => '',
            'type'         => in_array($type, $allowed, true) ? $type : 'text',
            'required'     => false,
            'placeholder'  => '',
            'help_text'    => '',
            'options_text' => '',
        ];
    }

    private function buildLeadProcess(): ?array
    {
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
                if ($key === '' && $label === '') continue;
                if ($key === '' || $label === '') {
                    $this->addError("lead_process_steps.{$si}.fields.{$fi}.key", 'Both key and label are required.');
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
                        ->map(fn ($i) => trim($i))->filter()->unique()->values()->all();
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
                $title   = trim((string) ($step['title'] ?? ''));
                $steps[] = ['title' => $title ?: ('Step ' . (count($steps) + 1)), 'fields' => $stepFields];
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) return null;
        return count($steps) > 0 ? ['mode' => $this->lead_process_mode, 'steps' => $steps] : null;
    }

    private function normalizeLeadProcess(mixed $process): array
    {
        if (!is_array($process)) return ['mode' => 'single', 'steps' => []];

        $mode  = in_array($process['mode'] ?? null, ['single', 'stepper'], true) ? $process['mode'] : 'single';
        $steps = [];
        foreach (($process['steps'] ?? []) as $rawStep) {
            if (!is_array($rawStep)) continue;
            $fields = [];
            foreach (($rawStep['fields'] ?? []) as $rawField) {
                if (!is_array($rawField)) continue;
                $optText = collect($rawField['options'] ?? [])
                    ->map(fn ($i) => trim((string) $i))->filter()->implode("\n");
                $fields[] = [
                    'uid'          => (string) ($rawField['uid'] ?? Str::uuid()),
                    'key'          => strtolower(trim((string) ($rawField['key'] ?? ''))),
                    'label'        => trim((string) ($rawField['label'] ?? '')),
                    'type'         => (string) ($rawField['type'] ?? 'text'),
                    'required'     => (bool) ($rawField['required'] ?? false),
                    'placeholder'  => (string) ($rawField['placeholder'] ?? ''),
                    'help_text'    => (string) ($rawField['help_text'] ?? ''),
                    'options_text' => $optText,
                ];
            }
            if (count($fields) === 0) $fields[] = $this->newLeadField();
            $steps[] = ['title' => trim((string) ($rawStep['title'] ?? 'Step')) ?: 'Step', 'fields' => $fields];
        }
        return ['mode' => $mode, 'steps' => $steps];
    }

    public function render()
    {
        return view('livewire.admin.campaign-modal', [
            'allInGroups' => InGroup::orderBy('name')->get(),
            'allCidGroups' => CidGroup::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
