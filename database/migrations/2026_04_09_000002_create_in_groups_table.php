<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("in_groups", function (Blueprint $table) {
      $table->id();
      $table->string("name");
      $table->text("description")->nullable();
      $table->boolean("is_active")->default(true);
      $table->unsignedSmallInteger("queue_priority")->default(1); // higher = answered first
      $table
        ->enum("agent_routing", [
          "ring_all",
          "round_robin",
          "fewest_calls",
          "longest_idle",
        ])
        ->default("ring_all");
      $table->unsignedSmallInteger("max_wait_seconds")->nullable(); // null = no limit
      $table
        ->enum("drop_action", ["hangup", "transfer", "voicemail"])
        ->default("hangup");
      $table->string("drop_destination")->nullable(); // phone number or voicemail URL
      $table->string("web_form_url")->nullable(); // screen-pop URL delivered to agent browser
      $table->string("hold_music_url")->nullable(); // URL of hold music audio file
      $table
        ->enum("after_hours_action", ["hangup", "transfer", "voicemail"])
        ->default("hangup");
      $table->string("after_hours_destination")->nullable();
      $table->json("hours_json")->nullable(); // null = always open; see note below
      /*
       * hours_json format:
       * {
       *   "mon": {"open": "08:00", "close": "17:00"},
       *   "tue": {"open": "08:00", "close": "17:00"},
       *   ...
       *   "sun": null   <- null = closed all day
       * }
       * Times are in the group's timezone.
       */
      $table->string("timezone")->default("UTC");
      $table
        ->foreignId("campaign_id")
        ->nullable()
        ->constrained()
        ->nullOnDelete(); // optional reporting link
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("in_groups");
  }
};
