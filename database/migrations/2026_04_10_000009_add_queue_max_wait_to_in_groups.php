<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('in_groups', function (Blueprint $table) {
            // How long (seconds) to keep a caller in the hold queue before
            // giving up and executing drop_action. 0 = disabled (drop immediately).
            $table->unsignedSmallInteger('queue_max_wait_seconds')
                  ->default(300)
                  ->after('max_wait_seconds')
                  ->comment('Max seconds caller waits in hold queue (0 = no queue)');
        });
    }

    public function down(): void
    {
        Schema::table('in_groups', function (Blueprint $table) {
            $table->dropColumn('queue_max_wait_seconds');
        });
    }
};
