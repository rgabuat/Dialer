<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('cid_number_id')
                  ->nullable()
                  ->after('campaign_id')
                  ->constrained('cid_numbers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['cid_number_id']);
            $table->dropColumn('cid_number_id');
        });
    }
};
