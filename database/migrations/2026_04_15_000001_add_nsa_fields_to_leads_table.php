<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make store_id nullable (raw SQL — no doctrine/dbal required)
        DB::statement('ALTER TABLE `leads` MODIFY COLUMN `store_id` BIGINT UNSIGNED NULL');

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('conversation_id')->nullable()->after('id')
                ->constrained('conversations')->nullOnDelete();

            $table->string('lead_type')->nullable()->after('email');
            $table->date('move_in_date')->nullable()->after('lead_type');
            $table->string('reason_for_storage')->nullable()->after('move_in_date');
            $table->string('types_of_items')->nullable()->after('reason_for_storage');
            $table->string('duration')->nullable()->after('types_of_items');
            $table->string('property_protection')->nullable()->after('duration');
            $table->string('promo')->nullable()->after('property_protection');
            $table->boolean('admin_fee_credit')->default(false)->after('promo');
            $table->string('unit_size')->nullable()->after('admin_fee_credit');
            $table->boolean('notify_sms')->default(false)->after('unit_size');
            $table->boolean('notify_email')->default(true)->after('notify_sms');
            $table->string('notify_email_address')->nullable()->after('notify_email');
            $table->json('selected_units')->nullable()->after('notify_email_address');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropColumn([
                'conversation_id',
                'lead_type', 'move_in_date', 'reason_for_storage', 'types_of_items', 'duration',
                'property_protection', 'promo', 'admin_fee_credit', 'unit_size',
                'notify_sms', 'notify_email', 'notify_email_address', 'selected_units',
            ]);
        });

        // Revert store_id to NOT NULL
        DB::statement('ALTER TABLE `leads` MODIFY COLUMN `store_id` BIGINT UNSIGNED NOT NULL');
    }
};
