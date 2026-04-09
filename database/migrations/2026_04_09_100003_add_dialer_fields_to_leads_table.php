<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('call_list_id')->nullable()->after('store_id')->constrained('call_lists')->nullOnDelete();
            $table->string('status')->default('NEW')->after('call_list_id');
            $table->timestamp('last_called_at')->nullable()->after('status');
            $table->unsignedSmallInteger('call_count')->default(0)->after('last_called_at');
            $table->string('timezone')->nullable()->after('call_count');
            $table->string('address')->nullable()->after('timezone');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('zip')->nullable()->after('state');
            $table->string('country')->nullable()->after('zip');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['call_list_id']);
            $table->dropColumn([
                'call_list_id', 'status', 'last_called_at', 'call_count',
                'timezone', 'address', 'city', 'state', 'zip', 'country',
            ]);
        });
    }
};
