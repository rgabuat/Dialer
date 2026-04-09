<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('disposition_id')->nullable()->after('campaign_id')->constrained()->nullOnDelete();
            $table->text('disposition_notes')->nullable()->after('disposition_id');
            $table->foreignId('lead_id')->nullable()->after('disposition_notes')->constrained()->nullOnDelete();
            $table->timestamp('wrapped_at')->nullable()->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disposition_id');
            $table->dropColumn('disposition_notes');
            $table->dropConstrainedForeignId('lead_id');
            $table->dropColumn('wrapped_at');
        });
    }
};
