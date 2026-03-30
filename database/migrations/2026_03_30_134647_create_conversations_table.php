<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create("conversations", function (Blueprint $table) {
      $table->id();
      $table->string("channel")->default("voice"); // voice | sms | email | chat
      $table->string("direction")->default("inbound"); // inbound | outbound
      $table->string("status")->default("in_progress"); // in_progress | completed | queued | abandoned
      $table->string("contact_name")->nullable();
      $table->string("contact_phone")->nullable();
      $table->string("queue")->nullable();
      $table->text("detail_preview")->nullable(); // first line of message / call note
      $table->unsignedInteger("duration_seconds")->nullable();
      $table
        ->foreignId("campaign_id")
        ->nullable()
        ->constrained()
        ->nullOnDelete();
      $table
        ->foreignId("assigned_to")
        ->nullable()
        ->constrained("users")
        ->nullOnDelete();
      $table
        ->foreignId("completed_by")
        ->nullable()
        ->constrained("users")
        ->nullOnDelete();
      $table->timestamp("started_at")->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists("conversations");
  }
};
