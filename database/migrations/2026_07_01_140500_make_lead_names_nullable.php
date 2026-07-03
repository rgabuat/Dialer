<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `leads` MODIFY COLUMN `first_name` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `leads` MODIFY COLUMN `last_name` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE `leads` SET `first_name` = 'Unknown' WHERE `first_name` IS NULL OR `first_name` = ''");
        DB::statement("UPDATE `leads` SET `last_name` = 'Lead' WHERE `last_name` IS NULL OR `last_name` = ''");
        DB::statement('ALTER TABLE `leads` MODIFY COLUMN `first_name` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `leads` MODIFY COLUMN `last_name` VARCHAR(255) NOT NULL');
    }
};
