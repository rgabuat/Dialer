<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("ivr_menu_options", function (Blueprint $table) {
      $table->id();
      $table->foreignId("ivr_menu_id")->constrained()->cascadeOnDelete();
      $table->string("digit", 1); // 0-9, *, #
      $table->string("description")->nullable(); // label e.g. "Sales"
      $table->enum("action", [
        "in_group",
        "ivr_menu",
        "transfer",
        "voicemail",
        "hangup",
      ]);
      $table->string("destination")->nullable(); // in_group.id | ivr_menu.id | phone number | voicemail URL
      $table->timestamps();

      $table->unique(["ivr_menu_id", "digit"]);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("ivr_menu_options");
  }
};
