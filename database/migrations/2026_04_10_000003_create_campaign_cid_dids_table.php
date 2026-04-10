<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_cid_dids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('did_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0); // controls rotation sequence
            $table->timestamps();

            $table->unique(['campaign_id', 'did_id']);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            // When true, outbound callerId is pulled round-robin from campaign_cid_dids
            $table->boolean('cid_rotation')->default(false)->after('caller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_cid_dids');
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('cid_rotation');
        });
    }
};
