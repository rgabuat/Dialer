<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_status_types', function (Blueprint $table) {
            $table->boolean('is_inbound')->default(false)->after('is_available')
                  ->comment('Whether agents on this status receive inbound calls');
        });

        // Only "phones" status should receive inbound calls
        DB::table('agent_status_types')->where('slug', 'phones')->update(['is_inbound' => true]);
    }

    public function down(): void
    {
        Schema::table('agent_status_types', function (Blueprint $table) {
            $table->dropColumn('is_inbound');
        });
    }
};
