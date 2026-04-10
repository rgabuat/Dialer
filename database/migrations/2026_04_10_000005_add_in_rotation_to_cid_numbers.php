<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cid_numbers', function (Blueprint $table) {
            $table->boolean('in_rotation')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('cid_numbers', function (Blueprint $table) {
            $table->dropColumn('in_rotation');
        });
    }
};
