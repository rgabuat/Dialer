<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CID Groups — named, campaign-exclusive pools of outbound caller IDs
        Schema::create('cid_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Each CID number may belong to exactly one CID group
        Schema::table('cid_numbers', function (Blueprint $table) {
            $table->foreignId('cid_group_id')
                ->nullable()
                ->after('in_rotation')
                ->constrained('cid_groups')
                ->nullOnDelete();
        });

        // Each campaign may reference exactly one CID group (exclusive — enforced in app logic)
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('cid_group_id')
                ->nullable()
                ->after('cid_rotation')
                ->constrained('cid_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['cid_group_id']);
            $table->dropColumn('cid_group_id');
        });

        Schema::table('cid_numbers', function (Blueprint $table) {
            $table->dropForeign(['cid_group_id']);
            $table->dropColumn('cid_group_id');
        });

        Schema::dropIfExists('cid_groups');
    }
};
