<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->enum('type', ['OUTBOUND', 'INBOUND', 'BLENDED'])->default('OUTBOUND')->after('is_active');
            $table->enum('dial_mode', ['MANUAL', 'PREVIEW', 'PROGRESSIVE', 'PREDICTIVE'])->default('MANUAL')->after('type');
            $table->decimal('dial_level', 4, 2)->default(1.00)->after('dial_mode');
            $table->string('caller_id')->nullable()->after('dial_level');
            $table->text('script')->nullable()->after('caller_id');
            $table->unsignedSmallInteger('acw_seconds')->default(0)->after('script');
            $table->unsignedSmallInteger('hopper_level')->default(50)->after('acw_seconds');
            $table->unsignedSmallInteger('max_calls')->nullable()->after('hopper_level');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['type', 'dial_mode', 'dial_level', 'caller_id', 'script', 'acw_seconds', 'hopper_level', 'max_calls']);
        });
    }
};
