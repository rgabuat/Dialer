<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use App\Models\Campaign;

class CampaignCreate extends Component
{
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public string $type = 'OUTBOUND';
    public string $dial_mode = 'MANUAL';
    public string $dial_level = '1.00';
    public string $caller_id = '';
    public string $script = '';
    public int $acw_seconds = 0;
    public int $hopper_level = 50;
    public ?int $max_calls = null;

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
        ]);

        Campaign::create([
            'name'         => $this->name,
            'description'  => $this->description ?: null,
            'is_active'    => $this->is_active,
            'type'         => $this->type,
            'dial_mode'    => $this->dial_mode,
            'dial_level'   => $this->dial_level,
            'caller_id'    => $this->caller_id ?: null,
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
        ]);

        session()->flash('success', 'Campaign created successfully.');

        $this->redirect(route('campaigns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.campaign.campaign-create')
            ->layout('components.layouts.app');
    }
}
