<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceSetting extends Model
{
    protected $fillable = [
        'tts_completed',
        'tts_busy',
        'tts_no_answer',
        'tts_failed',
        'tts_canceled',
        'tts_voice',
        'tts_language',
        'inbound_timeout',
        'greeting_message',
        'hold_music_url',
        'recording_enabled',
        'recording_channels',
    ];

    protected $casts = [
        'inbound_timeout'    => 'integer',
        'recording_enabled'  => 'boolean',
    ];

    /**
     * Always return the single settings row, creating it with defaults if absent.
     */
    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'tts_completed'      => 'Thank you for calling. Goodbye.',
            'tts_busy'           => 'We are sorry, no agents are currently available. Please call back later. Goodbye.',
            'tts_no_answer'      => 'We are sorry, no agents are currently available. Please call back later. Goodbye.',
            'tts_failed'         => 'We are sorry, we encountered an issue. Please call back later. Goodbye.',
            'tts_canceled'       => 'The call was ended. Thank you. Goodbye.',
            'tts_voice'          => 'alice',
            'tts_language'       => 'en-US',
            'inbound_timeout'    => 20,
            'greeting_message'   => null,
            'hold_music_url'     => 'https://demo.twilio.com/docs/classic.mp3',
            'recording_enabled'  => false,
            'recording_channels' => 'both',
        ]);
    }
}
