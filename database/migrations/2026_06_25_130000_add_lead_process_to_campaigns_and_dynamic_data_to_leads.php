<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->json('lead_process')->nullable()->after('recording_channels');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->json('dynamic_data')->nullable()->after('selected_units');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('lead_process');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('dynamic_data');
        });
    }
};
