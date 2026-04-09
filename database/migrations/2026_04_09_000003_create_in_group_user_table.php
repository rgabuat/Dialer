<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("in_group_user", function (Blueprint $table) {
      $table->id();
      $table->foreignId("in_group_id")->constrained()->cascadeOnDelete();
      $table->foreignId("user_id")->constrained()->cascadeOnDelete();
      $table->unsignedSmallInteger("priority")->default(1); // reserved for future skill-based routing
      $table->timestamp("last_call_at")->nullable(); // tracks round-robin rotation
      $table->boolean("is_active")->default(true);
      $table->timestamps();

      $table->unique(["in_group_id", "user_id"]);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("in_group_user");
  }
};
