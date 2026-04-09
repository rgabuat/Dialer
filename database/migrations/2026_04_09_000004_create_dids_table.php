<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("dids", function (Blueprint $table) {
      $table->id();
      $table->string("phone_number")->unique(); // E.164 format, e.g. +15551234567
      $table->string("description")->nullable();
      $table
        ->foreignId("in_group_id")
        ->nullable()
        ->constrained()
        ->nullOnDelete();
      $table
        ->foreignId("ivr_menu_id")
        ->nullable()
        ->constrained()
        ->nullOnDelete();
      $table
        ->foreignId("campaign_id")
        ->nullable()
        ->constrained()
        ->nullOnDelete(); // optional outbound reporting link
      $table->boolean("is_active")->default(true);
      $table->timestamps();

      // Only one of in_group_id or ivr_menu_id should be set — enforced at app layer
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("dids");
  }
};
