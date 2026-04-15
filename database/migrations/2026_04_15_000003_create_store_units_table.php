<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('size');
            $table->string('category');
            $table->unsignedSmallInteger('available')->default(0);
            $table->decimal('street_rate', 8, 2);
            $table->decimal('push_rate', 8, 2);
            $table->json('features')->nullable();
            $table->json('promos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_units');
    }
};
