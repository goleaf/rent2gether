<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_bulk_action_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action_type');
            $table->string('target_type');
            $table->string('status')->default('draft');
            $table->unsignedInteger('selected_count')->default(0);
            $table->unsignedInteger('affected_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->json('payload_json')->nullable();
            $table->json('preview_json')->nullable();
            $table->json('result_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'host_bulk_batches_user_status_index');
            $table->index(['user_id', 'action_type'], 'host_bulk_batches_user_action_index');
            $table->index('action_type', 'host_bulk_batches_action_index');
            $table->index('target_type', 'host_bulk_batches_target_type_index');
            $table->index('status', 'host_bulk_batches_status_index');
            $table->index('started_at', 'host_bulk_batches_started_index');
            $table->index('completed_at', 'host_bulk_batches_completed_index');
        });

        Schema::create('host_bulk_action_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('host_bulk_action_batches')->cascadeOnDelete();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->string('status')->default('pending');
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status'], 'host_bulk_items_batch_status_index');
            $table->index(['target_type', 'target_id'], 'host_bulk_items_target_index');
            $table->index('status', 'host_bulk_items_status_index');
        });

        Schema::create('host_bulk_action_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('host_bulk_action_batches')->nullOnDelete();
            $table->string('action_type');
            $table->string('target_type');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('message');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'host_bulk_logs_user_created_index');
            $table->index('batch_id', 'host_bulk_logs_batch_index');
            $table->index('action_type', 'host_bulk_logs_action_index');
            $table->index(['target_type', 'target_id'], 'host_bulk_logs_target_index');
        });

        Schema::create('host_cleaning_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('planned');
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time')->nullable();
            $table->string('reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'host_cleaning_tasks_user_status_index');
            $table->index(['property_id', 'status'], 'host_cleaning_tasks_property_status_index');
            $table->index(['room_id', 'status'], 'host_cleaning_tasks_room_status_index');
            $table->index(['sleeping_place_id', 'status'], 'host_cleaning_tasks_place_status_index');
            $table->index('booking_id', 'host_cleaning_tasks_booking_index');
            $table->index('scheduled_date', 'host_cleaning_tasks_scheduled_date_index');
            $table->index('status', 'host_cleaning_tasks_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_cleaning_tasks');
        Schema::dropIfExists('host_bulk_action_logs');
        Schema::dropIfExists('host_bulk_action_items');
        Schema::dropIfExists('host_bulk_action_batches');
    }
};
