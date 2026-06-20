<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_check_outs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('check_out_date');
            $table->string('planned_check_out_time')->nullable();
            $table->timestamp('actual_check_out_at')->nullable();
            $table->string('check_out_method')->nullable();
            $table->boolean('keys_returned')->default(false);
            $table->unsignedTinyInteger('keys_returned_count')->nullable();
            $table->boolean('access_card_returned')->default(false);
            $table->boolean('electronic_key_disabled')->default(false);
            $table->boolean('locker_emptied')->default(false);
            $table->boolean('locker_key_returned')->default(false);
            $table->boolean('personal_items_taken')->default(false);
            $table->boolean('bedding_returned')->default(false);
            $table->boolean('towel_returned')->default(false);
            $table->boolean('sleeping_place_free')->default(false);
            $table->boolean('room_checked')->default(false);
            $table->boolean('sleeping_place_checked')->default(false);
            $table->boolean('has_damage')->default(false);
            $table->boolean('has_extra_dirty')->default(false);
            $table->boolean('has_forgotten_items')->default(false);
            $table->boolean('needs_deposit_deduction')->default(false);
            $table->decimal('deposit_deduction_amount', 10, 2)->nullable();
            $table->text('deposit_deduction_reason')->nullable();
            $table->string('after_place_photo_path')->nullable();
            $table->string('after_room_photo_path')->nullable();
            $table->json('damage_photo_paths_json')->nullable();
            $table->timestamp('guest_confirmed_at')->nullable();
            $table->timestamp('host_confirmed_at')->nullable();
            $table->string('status')->default('not_started');
            $table->string('problem_status')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'check_out_date']);
            $table->index(['room_id', 'check_out_date']);
            $table->index(['sleeping_place_id', 'check_out_date']);
            $table->index(['check_out_date', 'status']);
            $table->index('has_damage');
            $table->index('has_extra_dirty');
            $table->index('has_forgotten_items');
            $table->index('needs_deposit_deduction');
            $table->index('status');
        });

        Schema::create('booking_check_out_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->string('item_key');
            $table->string('label_key');
            $table->string('status')->default('pending');
            $table->boolean('required')->default(false);
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['booking_check_out_id', 'status']);
            $table->index(['booking_check_out_id', 'item_key']);
            $table->index('required');
        });

        Schema::create('booking_check_out_issue_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('issue_type');
            $table->string('severity')->default('medium');
            $table->text('description')->nullable();
            $table->json('photo_paths_json')->nullable();
            $table->string('status')->default('open');
            $table->boolean('deposit_related')->default(false);
            $table->boolean('repair_needed')->default(false);
            $table->boolean('cleaning_needed')->default(false);
            $table->text('host_response')->nullable();
            $table->text('guest_response')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['booking_check_out_id', 'status']);
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('issue_type');
            $table->index('severity');
            $table->index('deposit_related');
            $table->index('repair_needed');
            $table->index('cleaning_needed');
        });

        Schema::create('booking_forgotten_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('item_name')->nullable();
            $table->text('description')->nullable();
            $table->json('photo_paths_json')->nullable();
            $table->string('storage_location')->nullable();
            $table->string('status')->default('found');
            $table->timestamp('guest_notified_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('disposed_at')->nullable();
            $table->date('keep_until')->nullable();
            $table->timestamps();

            $table->index('booking_check_out_id');
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('status');
            $table->index('keep_until');
        });

        Schema::create('booking_deposit_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('decision')->default('no_deposit');
            $table->decimal('deduction_amount', 10, 2)->nullable();
            $table->decimal('return_amount', 10, 2)->nullable();
            $table->text('reason')->nullable();
            $table->json('evidence_photo_paths_json')->nullable();
            $table->text('guest_comment')->nullable();
            $table->text('host_comment')->nullable();
            $table->string('status')->default('pending_review');
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('guest_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('booking_check_out_id');
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('decision');
            $table->index('status');
        });

        Schema::create('host_inspection_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->constrained('booking_check_outs')->nullOnDelete();
            $table->string('status')->default('planned');
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time')->nullable();
            $table->string('reason')->nullable();
            $table->json('checklist_json')->nullable();
            $table->json('result_json')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index('booking_id');
            $table->index('booking_check_out_id');
            $table->index('scheduled_date');
            $table->index('status');
        });

        Schema::create('booking_review_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reviewer_role');
            $table->string('status')->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index(['reviewer_user_id', 'status']);
            $table->index(['reviewee_user_id', 'status']);
            $table->index('reviewer_role');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_review_requests');
        Schema::dropIfExists('host_inspection_tasks');
        Schema::dropIfExists('booking_deposit_decisions');
        Schema::dropIfExists('booking_forgotten_items');
        Schema::dropIfExists('booking_check_out_issue_reports');
        Schema::dropIfExists('booking_check_out_checklist_items');
        Schema::dropIfExists('booking_check_outs');
    }
};
