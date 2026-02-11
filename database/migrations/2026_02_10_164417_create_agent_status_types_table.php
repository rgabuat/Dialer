<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agent_status_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Phones, Break
            $table->string('slug')->unique();    // phones, break
            $table->string('color')->nullable(); // hex or tailwind key
            $table->string('description')->nullable();

            $table->boolean('is_available')->default(false);
            $table->boolean('is_productive')->default(true);
            $table->boolean('is_break')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_status_types');
    }
};
