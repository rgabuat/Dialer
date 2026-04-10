<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('tts_voice', 50)->default('alice')->after('cid_rotation');
            $table->string('tts_language', 20)->default('en-US')->after('tts_voice');
            $table->string('tts_completed', 500)->default('Thank you for calling. Goodbye.')->after('tts_language');
            $table->string('tts_busy', 500)->default('We are sorry, no agents are currently available. Please call back later. Goodbye.')->after('tts_completed');
            $table->string('tts_no_answer', 500)->default('We are sorry, no agents are currently available. Please call back later. Goodbye.')->after('tts_busy');
            $table->string('tts_failed', 500)->default('We are sorry, we encountered an issue. Please call back later. Goodbye.')->after('tts_no_answer');
            $table->string('tts_canceled', 500)->default('The call was ended. Thank you. Goodbye.')->after('tts_failed');
            $table->string('greeting_message', 500)->nullable()->after('tts_canceled');
            $table->string('hold_music_url', 1000)->nullable()->after('greeting_message');
            $table->boolean('recording_enabled')->default(false)->after('hold_music_url');
            $table->string('recording_channels', 20)->default('both')->after('recording_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'tts_voice',
                'tts_language',
                'tts_completed',
                'tts_busy',
                'tts_no_answer',
                'tts_failed',
                'tts_canceled',
                'greeting_message',
                'hold_music_url',
                'recording_enabled',
                'recording_channels',
            ]);
        });
    }
};
