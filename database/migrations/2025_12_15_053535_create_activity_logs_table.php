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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            /**
             * ACTOR — who performed the action
             * Can be User, Admin, or System
             */
            $table->nullableMorphs('actor'); // actor_type, actor_id

            /**
             * SUBJECT — who/what was affected
             * User, Call, Campaign, etc.
             */
            $table->nullableMorphs('subject'); // subject_type, subject_id

            /**
             * LOG CLASSIFICATION
             */
            $table->string('type', 20)->index();
            // activity | audit | system | call | security

            /**
             * SEVERITY — risk / importance level
             */
            $table->enum('severity', ['info', 'warning', 'critical'])
                ->default('info')
                ->index();

            /**
             * EVENT DETAILS
             */
            $table->string('event', 50);   // updated, login, permission_changed
            $table->string('action');      // Human-readable message

            /**
             * EXTRA CONTEXT
             */
            $table->json('properties')->nullable();

            /**
             * GROUPING (one user intent / request)
             */
            $table->uuid('batch_id')->nullable()->index();

            /**
             * REQUEST CONTEXT
             */
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('source', 20)->default('web');
            // web | api | job | system

            /**
             * MULTI-TENANT (optional but future-proof)
             */
            $table->foreignId('tenant_id')->nullable()->index();

            /**
             * TIMING
             */
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();

            /**
             * INDEXES FOR AUDIT & SECURITY QUERIES
             */
            $table->index(['type', 'event']);
            $table->index(['performed_at']);
            $table->index(['severity', 'performed_at']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
