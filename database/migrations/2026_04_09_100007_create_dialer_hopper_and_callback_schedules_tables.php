<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dialer_hopper', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number', 30);
            $table->enum('status', ['pending', 'dialing', 'completed', 'skipped'])->default('pending');
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'lead_id']);
            $table->index(['campaign_id', 'status', 'scheduled_at']);
        });

        Schema::create('callback_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['campaign_id', 'status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_schedules');
        Schema::dropIfExists('dialer_hopper');
    }
};
