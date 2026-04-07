<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voice_settings', function (Blueprint $table) {
            $table->id();

            // ── TTS messages per DialCallStatus ─────────────────────────────────
            $table->string('tts_completed')->default('Thank you for calling. Goodbye.');
            $table->string('tts_busy')->default('We are sorry, no agents are currently available. Please call back later. Goodbye.');
            $table->string('tts_no_answer')->default('We are sorry, no agents are currently available. Please call back later. Goodbye.');
            $table->string('tts_failed')->default('We are sorry, we encountered an issue. Please call back later. Goodbye.');
            $table->string('tts_canceled')->default('The call was ended. Thank you. Goodbye.');

            // ── TTS voice & language ─────────────────────────────────────────────
            $table->string('tts_voice')->default('alice');        // e.g. alice, man, woman
            $table->string('tts_language')->default('en-US');     // e.g. en-US, es-US

            // ── Inbound routing ─────────────────────────────────────────────────
            $table->unsignedSmallInteger('inbound_timeout')->default(20);   // seconds to ring before giving up
            $table->string('greeting_message')->nullable();                 // played to caller while agents ring

            // ── Hold music ──────────────────────────────────────────────────────
            $table->string('hold_music_url')->default('https://demo.twilio.com/docs/classic.mp3');

            // ── Recording ───────────────────────────────────────────────────────
            $table->boolean('recording_enabled')->default(false);
            $table->string('recording_channels')->default('both'); // both | inbound | outbound

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voice_settings');
    }
};
