<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\VoiceSetting;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class Voice extends Component
{
    // TTS messages per DialCallStatus
    public string $tts_completed  = '';
    public string $tts_busy       = '';
    public string $tts_no_answer  = '';
    public string $tts_failed     = '';
    public string $tts_canceled   = '';

    // TTS voice & language
    public string $tts_voice    = 'alice';
    public string $tts_language = 'en-US';

    // Inbound routing
    public int    $inbound_timeout   = 20;
    public string $greeting_message  = '';

    // Hold music
    public string $hold_music_url = '';

    // Recording
    public bool   $recording_enabled  = false;
    public string $recording_channels = 'both';

    public function mount(): void
    {
        $s = VoiceSetting::instance();

        $this->tts_completed     = (string) $s->tts_completed;
        $this->tts_busy          = (string) $s->tts_busy;
        $this->tts_no_answer     = (string) $s->tts_no_answer;
        $this->tts_failed        = (string) $s->tts_failed;
        $this->tts_canceled      = (string) $s->tts_canceled;
        $this->tts_voice         = (string) $s->tts_voice;
        $this->tts_language      = (string) $s->tts_language;
        $this->inbound_timeout   = (int) $s->inbound_timeout;
        $this->greeting_message  = (string) $s->greeting_message;
        $this->hold_music_url    = (string) $s->hold_music_url;
        $this->recording_enabled  = (bool) $s->recording_enabled;
        $this->recording_channels = (string) $s->recording_channels;
    }

    public function save(): void
    {
        $this->validate([
            'tts_completed'     => 'required|string|max:500',
            'tts_busy'          => 'required|string|max:500',
            'tts_no_answer'     => 'required|string|max:500',
            'tts_failed'        => 'required|string|max:500',
            'tts_canceled'      => 'required|string|max:500',
            'tts_voice'         => 'required|in:alice,man,woman',
            'tts_language'      => 'required|string|max:10',
            'inbound_timeout'   => 'required|integer|min:5|max:120',
            'greeting_message'  => 'nullable|string|max:500',
            'hold_music_url'    => 'required|url|max:255',
            'recording_enabled'  => 'boolean',
            'recording_channels' => 'required|in:both,inbound,outbound',
        ]);

        $s = VoiceSetting::instance();

        $before = $s->only([
            'tts_completed', 'tts_busy', 'tts_no_answer', 'tts_failed', 'tts_canceled',
            'tts_voice', 'tts_language', 'inbound_timeout', 'greeting_message',
            'hold_music_url', 'recording_enabled', 'recording_channels',
        ]);

        $s->update([
            'tts_completed'      => $this->tts_completed,
            'tts_busy'           => $this->tts_busy,
            'tts_no_answer'      => $this->tts_no_answer,
            'tts_failed'         => $this->tts_failed,
            'tts_canceled'       => $this->tts_canceled,
            'tts_voice'          => $this->tts_voice,
            'tts_language'       => $this->tts_language,
            'inbound_timeout'    => $this->inbound_timeout,
            'greeting_message'   => $this->greeting_message ?: null,
            'hold_music_url'     => $this->hold_music_url,
            'recording_enabled'  => $this->recording_enabled,
            'recording_channels' => $this->recording_channels,
        ]);

        $after = $s->fresh()->only(array_keys($before));
        $changes = [];
        foreach ($after as $key => $val) {
            if ((string) ($before[$key] ?? '') !== (string) $val) {
                $changes[$key] = ['before' => $before[$key], 'after' => $val];
            }
        }

        if (!empty($changes)) {
            ActivityLogger::log(
                type:       'audit',
                event:      'settings.voice.updated',
                action:     'Updated voice call settings',
                actor:      Auth::user(),
                properties: $changes,
            );
        }

        $this->dispatch('toast', message: 'Voice settings saved.', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.voice')
            ->layout('livewire.settings.layout', ['title' => 'Voice Settings']);
    }
}
