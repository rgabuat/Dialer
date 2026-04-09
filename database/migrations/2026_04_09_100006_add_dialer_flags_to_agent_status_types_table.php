<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_status_types', function (Blueprint $table) {
            $table->boolean('handles_inbound')->default(false)->after('is_break');
            $table->boolean('handles_outbound')->default(false)->after('handles_inbound');
        });

        // Phones = inbound, Outbound = outbound
        \DB::table('agent_status_types')->where('slug', 'phones')->update(['handles_inbound' => true]);
        \DB::table('agent_status_types')->where('slug', 'outbound')->update(['handles_outbound' => true]);
    }

    public function down(): void
    {
        Schema::table('agent_status_types', function (Blueprint $table) {
            $table->dropColumn(['handles_inbound', 'handles_outbound']);
        });
    }
};
