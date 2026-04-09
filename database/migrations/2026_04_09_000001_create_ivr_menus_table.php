<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("ivr_menus", function (Blueprint $table) {
      $table->id();
      $table->string("name");
      $table->text("description")->nullable();
      $table->enum("greeting_type", ["tts", "audio"])->default("tts");
      $table->text("greeting_value")->nullable(); // TTS text or audio URL
      $table->unsignedSmallInteger("gather_timeout")->default(10); // seconds to wait for digit
      $table->unsignedTinyInteger("invalid_attempts_max")->default(3);
      $table
        ->enum("invalid_action", ["hangup", "repeat", "transfer"])
        ->default("repeat");
      $table->string("invalid_destination")->nullable(); // phone number if action=transfer
      $table->boolean("is_active")->default(true);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("ivr_menus");
  }
};
