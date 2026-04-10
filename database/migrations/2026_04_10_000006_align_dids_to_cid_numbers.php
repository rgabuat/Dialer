<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dids', function (Blueprint $table) {
            // Link each DID to a CID Number (the Twilio-imported number it handles)
            $table->foreignId('cid_number_id')
                ->nullable()
                ->after('id')
                ->constrained('cid_numbers')
                ->nullOnDelete();

            // Drop columns that are now sourced from the linked CidNumber
            $table->dropColumn(['description', 'twilio_sid']);

            // campaign_id was never used for routing — drop it
            if (Schema::hasColumn('dids', 'campaign_id')) {
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dids', function (Blueprint $table) {
            $table->dropForeign(['cid_number_id']);
            $table->dropColumn('cid_number_id');

            $table->string('description')->nullable()->after('phone_number');
            $table->string('twilio_sid')->nullable()->unique()->after('description');

            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
