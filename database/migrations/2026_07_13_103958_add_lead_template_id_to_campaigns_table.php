<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('lead_template_id')->nullable()->after('lead_process')
                  ->constrained('lead_templates')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['lead_template_id']);
            $table->dropColumn('lead_template_id');
        });
    }
};
