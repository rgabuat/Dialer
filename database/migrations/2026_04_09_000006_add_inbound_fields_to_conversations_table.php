<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table("conversations", function (Blueprint $table) {
      $table
        ->foreignId("in_group_id")
        ->nullable()
        ->after("campaign_id")
        ->constrained("in_groups")
        ->nullOnDelete();

      $table->timestamp("ended_at")->nullable()->after("started_at");
    });
  }

  public function down(): void
  {
    Schema::table("conversations", function (Blueprint $table) {
      $table->dropForeign(["in_group_id"]);
      $table->dropColumn(["in_group_id", "ended_at"]);
    });
  }
};
