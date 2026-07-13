<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\CidGroup;
use App\Models\InGroup;
use App\Models\LeadTemplate;
use Livewire\Attributes\On;
use Livewire\Component;

class CampaignModal extends Component
{
    public bool   $open = false;
    public string $mode = 'create';
    public int    $step = 1;
    public ?int   $campaignId = null;

    // Step 1: Details
    public string $name           = '';
    public string $description    = '';
    public bool   $is_active      = true;
    public string $type           = 'OUTBOUND';
    public string $script         = '';
    public bool   $script_enabled = false;

    // Step 2: Dialer
    public string $dial_mode    = 'MANUAL';
    public string $dial_level   = '1.00';
    public int    $acw_seconds  = 0;
    public int    $hopper_level = 50;
    public ?int   $max_calls    = null;

    // Step 3: Lead Template
    public ?int $lead_template_id = null;

    // Step 4 (merged): Inbound Groups
    public array $selectedInGroupIds = [];

    // Step 4 (merged): CID
    public string $caller_id    = '';
    public bool   $cid_rotation = false;
    public ?int   $cid_group_id = null;

    // Step 4 (merged) / Step 5: Voice & Recording
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

    // Step 5: Modules
    public array $modules = [];

    #[On('open-create')]
    public function openCreate(): void
    {
        $this->reset([
            'campaignId', 'name', 'description', 'type', 'script', 'script_enabled',
            'dial_mode', 'dial_level', 'acw_seconds', 'hopper_level', 'max_calls',
            'lead_template_id', 'selectedInGroupIds',
            'caller_id', 'cid_rotation', 'cid_group_id',
            'tts_voice', 'tts_language', 'greeting_message', 'hold_music_url',
            'tts_completed', 'tts_busy', 'tts_no_answer', 'tts_failed', 'tts_canceled',
            'recording_enabled', 'recording_channels', 'modules',
        ]);
        $this->resetErrorBag();

        $this->modules = collect(Campaign::MODULES)->map(fn ($m) => $m['default'])->toArray();
        $this->is_active          = true;
        $this->dial_mode          = 'MANUAL';
        $this->dial_level         = '1.00';
        $this->hopper_level       = 50;
        $this->type               = 'OUTBOUND';
        $this->tts_voice          = 'alice';
        $this->tts_language       = 'en-US';
        $this->recording_channels = 'both';
        $this->tts_completed      = 'Thank you for calling. Goodbye.';
        $this->tts_busy           = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_no_answer      = 'We are sorry, no agents are currently available. Please call back later. Goodbye.';
        $this->tts_failed         = 'We are sorry, we encountered an issue. Please call back later. Goodbye.';
        $this->tts_canceled       = 'The call was ended. Thank you. Goodbye.';
        $this->mode = 'create';
        $this->step = 1;
        $this->open = true;
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
        $this->script_enabled = (bool) ($campaign->script_enabled ?? false);

        $this->dial_mode     = $campaign->dial_mode ?? 'MANUAL';
        $this->dial_level    = (string) ($campaign->dial_level ?? '1.00');
        $this->acw_seconds   = (int) ($campaign->acw_seconds ?? 0);
        $this->hopper_level  = (int) ($campaign->hopper_level ?? 50);
        $this->max_calls     = $campaign->max_calls ? (int) $campaign->max_calls : null;

        $this->lead_template_id = $campaign->lead_template_id ? (int) $campaign->lead_template_id : null;

        $this->selectedInGroupIds = $campaign->inGroups()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $this->caller_id     = $campaign->caller_id ?? '';
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

        $saved = $campaign->modules ?? [];
        $this->modules = collect(Campaign::MODULES)
            ->mapWithKeys(fn ($m, $key) => [$key => (bool) ($saved[$key] ?? $m['default'])])
            ->toArray();

        $this->resetErrorBag();
        $this->mode = 'edit';
        $this->step = 1;
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateOnly('name', ['name' => 'required|string|max:255']);
            if ($this->getErrorBag()->has('name')) return;
        }
        if ($this->step < 5) $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function goToStep(int $s): void
    {
        if ($s >= 1 && $s <= 5) $this->step = $s;
    }

    public function save(): void
    {
        $this->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'is_active'          => 'boolean',
            'type'               => ['required', 'in:' . implode(',', Campaign::TYPES)],
            'script'             => 'nullable|string',
            'dial_mode'          => ['required', 'in:' . implode(',', Campaign::DIAL_MODES)],
            'dial_level'         => 'numeric|min:0.1|max:10',
            'acw_seconds'        => 'integer|min:0|max:3600',
            'hopper_level'       => 'integer|min:1|max:1000',
            'max_calls'          => 'nullable|integer|min:1|max:999',
            'lead_template_id'   => 'nullable|integer|exists:lead_templates,id',
            'selectedInGroupIds'   => 'array',
            'selectedInGroupIds.*' => 'integer|exists:in_groups,id',
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

        $payload = [
            'name'               => $this->name,
            'description'        => $this->description ?: null,
            'is_active'          => $this->is_active,
            'type'               => $this->type,
            'script'             => $this->script_enabled ? ($this->script ?: null) : null,
            'script_enabled'     => $this->script_enabled,
            'dial_mode'          => $this->dial_mode,
            'dial_level'         => $this->dial_level,
            'acw_seconds'        => $this->acw_seconds,
            'hopper_level'       => $this->hopper_level,
            'max_calls'          => $this->max_calls,
            'lead_template_id'   => $this->lead_template_id,
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
            'modules'            => $this->modules,
        ];

        if ($this->mode === 'create') {
            $campaign = Campaign::create($payload);
        } else {
            $campaign = Campaign::findOrFail($this->campaignId);
            $campaign->update($payload);
        }

        $ids = array_map('intval', $this->selectedInGroupIds);
        InGroup::whereIn('id', $ids)->update(['campaign_id' => $campaign->id]);
        InGroup::where('campaign_id', $campaign->id)->whereNotIn('id', $ids)->update(['campaign_id' => null]);

        $this->open = false;
        session()->flash('success', $this->mode === 'create' ? 'Campaign created.' : 'Campaign updated.');
        $this->dispatch('campaign-saved');
    }

    public function render()
    {
        return view('livewire.admin.campaign-modal', [
            'allInGroups'  => InGroup::orderBy('name')->get(),
            'allCidGroups' => CidGroup::where('is_active', true)->orderBy('name')->get(),
            'allTemplates' => LeadTemplate::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
