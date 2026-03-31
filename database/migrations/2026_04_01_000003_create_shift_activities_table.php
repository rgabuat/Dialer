<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("shift_activities", function (Blueprint $table) {
      $table->id();
      $table->foreignId("roster_shift_id")->constrained()->cascadeOnDelete();
      // phones | break | lunch | quality | outbound | support | escalations
      $table->string("activity_type");
      $table->time("start_time");
      $table->time("end_time");
      $table->unsignedSmallInteger("sort_order")->default(0);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("shift_activities");
  }
};
