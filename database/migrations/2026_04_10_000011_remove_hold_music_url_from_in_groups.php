<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('in_groups', function (Blueprint $table) {
            $table->dropColumn('hold_music_url');
        });
    }

    public function down(): void
    {
        Schema::table('in_groups', function (Blueprint $table) {
            $table->string('hold_music_url', 1000)->nullable()->after('web_form_url');
        });
    }
};
