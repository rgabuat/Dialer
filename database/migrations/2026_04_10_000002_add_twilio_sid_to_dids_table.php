<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dids', function (Blueprint $table) {
            // Twilio phone number SID (PN…) — links DID record to Twilio resource.
            // Allows webhook sync and prevents duplicate imports.
            $table->string('twilio_sid', 34)->nullable()->unique()->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('dids', function (Blueprint $table) {
            $table->dropColumn('twilio_sid');
        });
    }
};
