<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("staffing_intervals", function (Blueprint $table) {
      $table->id();
      $table->foreignId("roster_id")->constrained()->cascadeOnDelete();
      $table->tinyInteger("day_of_week"); // 0 = Sunday … 6 = Saturday
      $table->time("interval_start"); // 15-minute boundary e.g. "08:00", "08:15"
      $table->unsignedSmallInteger("agents_required")->default(0);
      $table->unsignedSmallInteger("calls_forecast")->default(0);
      $table->timestamps();

      $table->unique(["roster_id", "day_of_week", "interval_start"]);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("staffing_intervals");
  }
};
