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
    Schema::table("conversations", function (Blueprint $table) {
      if (!Schema::hasColumn("conversations", "call_sid")) {
        $table->string("call_sid")->nullable()->unique()->after("id");
      }
      if (!Schema::hasColumn("conversations", "ended_at")) {
        $table->timestamp("ended_at")->nullable()->after("started_at");
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table("conversations", function (Blueprint $table) {
      $table->dropColumn(["call_sid", "ended_at"]);
    });
  }
};
