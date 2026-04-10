<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CID Numbers: master list of Twilio phone numbers (outbound caller IDs)
        Schema::create('cid_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();   // E.164 e.g. +15551234567
            $table->string('twilio_sid', 34)->nullable()->unique(); // Twilio PN… SID
            $table->string('friendly_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Campaign ↔ CID rotation pool (replaces old campaign_cid_dids)
        Schema::create('campaign_cid_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cid_number_id')->constrained('cid_numbers')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['campaign_id', 'cid_number_id']);
        });

        // Drop the old DID-based pivot
        Schema::dropIfExists('campaign_cid_dids');
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_cid_numbers');
        Schema::dropIfExists('cid_numbers');

        // Recreate old pivot on rollback
        Schema::create('campaign_cid_dids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('did_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['campaign_id', 'did_id']);
        });
    }
};
