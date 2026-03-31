<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("roster_shifts", function (Blueprint $table) {
      $table->id();
      $table->foreignId("roster_id")->constrained()->cascadeOnDelete();
      $table->foreignId("user_id")->constrained()->cascadeOnDelete();
      $table->tinyInteger("day_of_week"); // 0 = Sunday … 6 = Saturday
      $table->time("start_time")->nullable();
      $table->time("end_time")->nullable();
      $table->string("location")->default("US");
      $table->decimal("total_hours", 4, 2)->nullable();
      $table->timestamps();

      // A user can only have one shift entry per day per roster
      $table->unique(["roster_id", "user_id", "day_of_week"]);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("roster_shifts");
  }
};
