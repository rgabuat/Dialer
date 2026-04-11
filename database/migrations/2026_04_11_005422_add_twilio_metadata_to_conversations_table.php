<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('caller_name', 150)->nullable()->after('contact_name');
            $table->string('caller_city', 100)->nullable()->after('caller_name');
            $table->string('caller_state', 100)->nullable()->after('caller_city');
            $table->string('caller_country', 10)->nullable()->after('caller_state');
            $table->string('caller_zip', 20)->nullable()->after('caller_country');
            $table->string('to_number', 50)->nullable()->after('caller_zip');
            $table->string('forwarded_from', 50)->nullable()->after('to_number');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['caller_name', 'caller_city', 'caller_state', 'caller_country', 'caller_zip', 'to_number', 'forwarded_from']);
        });
    }
};
