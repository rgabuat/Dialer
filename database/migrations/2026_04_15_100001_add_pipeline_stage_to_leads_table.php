<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('pipeline_stage')->nullable()->default('interested')->after('status');
            $table->string('source')->nullable()->after('pipeline_stage');
            $table->date('expires_at')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['pipeline_stage', 'source', 'expires_at']);
        });
    }
};
