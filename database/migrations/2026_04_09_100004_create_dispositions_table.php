<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('category', ['SALE', 'DNC', 'CALLBACK', 'RETRY', 'OTHER'])->default('OTHER');
            $table->boolean('is_dnc')->default(false);
            $table->boolean('requires_callback')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed global default dispositions (campaign_id = null)
        \DB::table('dispositions')->insert([
            ['campaign_id' => null, 'name' => 'Sale',           'code' => 'SALE',   'category' => 'SALE',     'is_dnc' => false, 'requires_callback' => false, 'is_active' => true, 'sort_order' => 1,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Do Not Call',    'code' => 'DNC',    'category' => 'DNC',      'is_dnc' => true,  'requires_callback' => false, 'is_active' => true, 'sort_order' => 2,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Callback',       'code' => 'CALLBK', 'category' => 'CALLBACK', 'is_dnc' => false, 'requires_callback' => true,  'is_active' => true, 'sort_order' => 3,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Scheduled CB',   'code' => 'CBHOLD', 'category' => 'CALLBACK', 'is_dnc' => false, 'requires_callback' => true,  'is_active' => true, 'sort_order' => 4,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'No Answer',      'code' => 'NA',     'category' => 'RETRY',    'is_dnc' => false, 'requires_callback' => false, 'is_active' => true, 'sort_order' => 5,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Busy',           'code' => 'BUSY',   'category' => 'RETRY',    'is_dnc' => false, 'requires_callback' => false, 'is_active' => true, 'sort_order' => 6,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Answering Mach', 'code' => 'AM',     'category' => 'RETRY',    'is_dnc' => false, 'requires_callback' => false, 'is_active' => true, 'sort_order' => 7,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Not Interested', 'code' => 'NI',     'category' => 'OTHER',    'is_dnc' => false, 'requires_callback' => false, 'is_active' => true, 'sort_order' => 8,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Wrong Number',   'code' => 'WN',     'category' => 'OTHER',    'is_dnc' => false, 'requires_callback' => false, 'is_active' => true, 'sort_order' => 9,  'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => null, 'name' => 'Drop',           'code' => 'DROP',   'category' => 'OTHER',    'is_dnc' => false, 'requires_callback' => false, 'is_active' => true, 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositions');
    }
};
