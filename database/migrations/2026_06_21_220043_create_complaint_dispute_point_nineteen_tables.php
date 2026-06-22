<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_cases', function (Blueprint $table): void {
            $table->id();
            $table->string('complaint_number')->unique();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('guest_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('against_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('submitted_by_type');
            $table->string('against_type');
            $table->string('complaint_type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('submitted');
            $table->string('title')->nullable();
            $table->text('description');
            $table->string('desired_resolution_type')->nullable();
            $table->string('resolution_type')->nullable();
            $table->string('resolution_status')->nullable();
            $table->boolean('guest_wants_refund')->default(false);
            $table->boolean('guest_wants_relocation')->default(false);
            $table->boolean('guest_wants_cancellation')->default(false);
            $table->boolean('guest_wants_compensation')->default(false);
            $table->boolean('host_wants_deposit_deduction')->default(false);
            $table->boolean('host_wants_guest_warning_future')->default(false);
            $table->boolean('host_wants_payment_resolution')->default(false);
            $table->decimal('amount_requested', 12, 2)->default(0);
            $table->string('currency', 3)->nullable();
            $table->timestamp('other_party_notified_at')->nullable();
            $table->timestamp('other_party_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('booking_cancellation_id')->nullable()->constrained('booking_cancellations')->nullOnDelete();
            $table->unsignedBigInteger('deposit_case_id')->nullable();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->unsignedBigInteger('cleaning_task_id')->nullable();
            $table->boolean('has_dispute')->default(false);
            $table->unsignedBigInteger('dispute_case_id')->nullable();
            $table->boolean('future_review_required')->default(false);
            $table->text('future_review_comment')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('booking_stay_id');
            $table->index('booking_check_in_id');
            $table->index('booking_check_out_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index(['reporter_user_id', 'status']);
            $table->index(['against_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index(['source_type', 'source_id']);
            $table->index('submitted_by_type');
            $table->index('against_type');
            $table->index('complaint_type');
            $table->index('severity');
            $table->index('status');
            $table->index('has_dispute');
            $table->index('booking_refund_id');
            $table->index('booking_relocation_id');
            $table->index('booking_cancellation_id');
            $table->index('deposit_case_id');
            $table->index('maintenance_request_id');
            $table->index('cleaning_task_id');
            $table->index('dispute_case_id');
            $table->index('created_at');
        });

        Schema::create('complaint_parties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_case_id')->constrained('complaint_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('party_type');
            $table->string('display_name_snapshot')->nullable();
            $table->string('role_in_case');
            $table->boolean('can_respond')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('user_id');
            $table->index('party_type');
            $table->index('role_in_case');
            $table->index('can_respond');
        });

        Schema::create('complaint_evidence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_case_id')->constrained('complaint_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('evidence_type');
            $table->string('media_type')->nullable();
            $table->string('evidence_role');
            $table->string('path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->unsignedBigInteger('message_thread_id')->nullable();
            $table->unsignedBigInteger('message_id')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host');
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('booking_id');
            $table->index('uploaded_by_user_id');
            $table->index('evidence_type');
            $table->index('evidence_role');
            $table->index('visibility');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('complaint_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_case_id')->constrained('complaint_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->boolean('accepts_problem')->nullable();
            $table->boolean('denies_problem')->nullable();
            $table->string('offered_resolution_type')->nullable();
            $table->decimal('offered_amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->boolean('requires_guest_response')->default(false);
            $table->boolean('requires_host_response')->default(false);
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('user_id');
            $table->index('response_type');
            $table->index('offered_resolution_type');
            $table->index('created_at');
        });

        Schema::create('complaint_resolution_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_case_id')->constrained('complaint_cases')->cascadeOnDelete();
            $table->string('resolution_type');
            $table->string('status')->default('offered');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('booking_cancellation_id')->nullable()->constrained('booking_cancellations')->nullOnDelete();
            $table->unsignedBigInteger('deposit_case_id')->nullable();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->unsignedBigInteger('cleaning_task_id')->nullable();
            $table->foreignId('offered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('resolution_type');
            $table->index('status');
            $table->index('booking_refund_id');
            $table->index('booking_relocation_id');
            $table->index('booking_cancellation_id');
            $table->index('deposit_case_id');
            $table->index('offered_by_user_id');
            $table->index('accepted_by_user_id');
        });

        Schema::create('complaint_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_case_id')->constrained('complaint_cases')->cascadeOnDelete();
            $table->string('action_type');
            $table->string('status')->default('pending');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('action_type');
            $table->index('status');
            $table->index(['source_type', 'source_id']);
            $table->index('assigned_to_user_id');
            $table->index('created_by_user_id');
            $table->index('due_at');
        });

        Schema::create('dispute_cases', function (Blueprint $table): void {
            $table->id();
            $table->string('dispute_number')->unique();
            $table->foreignId('complaint_case_id')->nullable()->constrained('complaint_cases')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('guest_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('opened_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('dispute_type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('opened');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount_disputed', 12, 2)->default(0);
            $table->string('currency', 3)->nullable();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->unsignedBigInteger('deposit_case_id')->nullable();
            $table->foreignId('booking_cancellation_id')->nullable()->constrained('booking_cancellations')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('booking_no_show_id')->nullable()->constrained('booking_no_shows')->nullOnDelete();
            $table->foreignId('host_unresponsive_case_id')->nullable()->constrained('booking_host_unresponsive_cases')->nullOnDelete();
            $table->foreignId('mismatch_report_id')->nullable()->constrained('booking_listing_mismatch_reports')->nullOnDelete();
            $table->boolean('booking_frozen')->default(false);
            $table->boolean('refund_frozen')->default(false);
            $table->boolean('deposit_frozen')->default(false);
            $table->boolean('host_payout_frozen')->default(false);
            $table->boolean('rating_impact_frozen')->default(false);
            $table->string('proposed_resolution_type')->nullable();
            $table->string('final_resolution_type')->nullable();
            $table->text('final_resolution_note')->nullable();
            $table->boolean('future_decision_required')->default(false);
            $table->text('future_decision_comment')->nullable();
            $table->timestamp('future_decided_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('booking_id');
            $table->index('booking_stay_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('opened_by_user_id');
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index(['source_type', 'source_id']);
            $table->index('dispute_type');
            $table->index('severity');
            $table->index('status');
            $table->index('booking_frozen');
            $table->index('refund_frozen');
            $table->index('deposit_frozen');
            $table->index('host_payout_frozen');
            $table->index('booking_refund_id');
            $table->index('deposit_case_id');
            $table->index('booking_cancellation_id');
            $table->index('booking_relocation_id');
            $table->index('booking_no_show_id');
            $table->index('host_unresponsive_case_id');
            $table->index('mismatch_report_id');
            $table->index('created_at');
        });

        Schema::create('dispute_evidence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dispute_case_id')->constrained('dispute_cases')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('evidence_type');
            $table->string('media_type')->nullable();
            $table->string('evidence_role');
            $table->string('path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host');
            $table->timestamps();

            $table->index('dispute_case_id');
            $table->index('uploaded_by_user_id');
            $table->index('evidence_type');
            $table->index('evidence_role');
            $table->index('visibility');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('dispute_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dispute_case_id')->constrained('dispute_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message_type');
            $table->text('message');
            $table->string('visibility')->default('guest_and_host');
            $table->timestamps();

            $table->index('dispute_case_id');
            $table->index('user_id');
            $table->index('message_type');
            $table->index('visibility');
            $table->index('created_at');
        });

        Schema::create('dispute_resolution_proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dispute_case_id')->constrained('dispute_cases')->cascadeOnDelete();
            $table->foreignId('proposed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution_type');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('description')->nullable();
            $table->boolean('guest_accepts')->nullable();
            $table->boolean('host_accepts')->nullable();
            $table->timestamp('guest_accepted_at')->nullable();
            $table->timestamp('host_accepted_at')->nullable();
            $table->string('status')->default('offered');
            $table->timestamps();

            $table->index('dispute_case_id');
            $table->index('proposed_by_user_id');
            $table->index('resolution_type');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('dispute_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dispute_case_id')->constrained('dispute_cases')->cascadeOnDelete();
            $table->string('decision_type');
            $table->string('resolution_type');
            $table->decimal('amount_to_guest', 12, 2)->default(0);
            $table->decimal('amount_to_host', 12, 2)->default(0);
            $table->decimal('deposit_return_amount', 12, 2)->default(0);
            $table->decimal('deposit_deduction_amount', 12, 2)->default(0);
            $table->decimal('host_payout_adjustment_amount', 12, 2)->default(0);
            $table->string('currency', 3)->nullable();
            $table->text('reason_summary')->nullable();
            $table->text('decision_note')->nullable();
            $table->string('decided_by_type');
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index('dispute_case_id');
            $table->index('decision_type');
            $table->index('resolution_type');
            $table->index('decided_by_type');
            $table->index('decided_by_user_id');
            $table->index('status');
        });

        Schema::create('complaint_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_case_id')->constrained('complaint_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('complaint_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_case_id')->constrained('complaint_cases')->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('complaint_case_id');
            $table->index('event_key');
            $table->index('event_type');
            $table->index(['source_type', 'source_id']);
            $table->index('user_id');
            $table->index('occurred_at');
        });

        Schema::create('dispute_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dispute_case_id')->constrained('dispute_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('dispute_case_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('dispute_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dispute_case_id')->constrained('dispute_cases')->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('dispute_case_id');
            $table->index('event_key');
            $table->index('event_type');
            $table->index(['source_type', 'source_id']);
            $table->index('user_id');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_events');
        Schema::dropIfExists('dispute_status_logs');
        Schema::dropIfExists('complaint_events');
        Schema::dropIfExists('complaint_status_logs');
        Schema::dropIfExists('dispute_decisions');
        Schema::dropIfExists('dispute_resolution_proposals');
        Schema::dropIfExists('dispute_messages');
        Schema::dropIfExists('dispute_evidence');
        Schema::dropIfExists('dispute_cases');
        Schema::dropIfExists('complaint_actions');
        Schema::dropIfExists('complaint_resolution_options');
        Schema::dropIfExists('complaint_responses');
        Schema::dropIfExists('complaint_evidence');
        Schema::dropIfExists('complaint_parties');
        Schema::dropIfExists('complaint_cases');
    }
};
