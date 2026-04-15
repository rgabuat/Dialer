<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('type')->nullable()->after('brand');
            $table->decimal('occupancy', 5, 2)->nullable()->after('type');
            $table->string('city')->nullable()->after('occupancy');
            $table->string('state', 10)->nullable()->after('city');
            $table->string('zip', 20)->nullable()->after('state');
            $table->string('country', 10)->nullable()->after('zip');
            $table->boolean('featured')->default(false)->after('country');
            $table->json('pricing')->nullable()->after('featured');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['type', 'occupancy', 'city', 'state', 'zip', 'country', 'featured', 'pricing']);
        });
    }
};
