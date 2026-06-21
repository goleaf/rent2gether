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
        Schema::create('host_unresponsive_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->nullable()->constrained('sleeping_places')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('pre_check_in_response_minutes')->default(240);
            $table->unsignedInteger('check_in_response_minutes')->default(60);
            $table->unsignedInteger('guest_waiting_outside_response_minutes')->default(20);
            $table->unsignedInteger('night_entry_response_minutes')->default(15);
            $table->unsignedInteger('urgent_response_minutes')->default(10);
            $table->boolean('notify_representative_if_available')->default(true);
            $table->boolean('auto_show_instructions_if_allowed')->default(true);
            $table->boolean('auto_block_no_show_while_active')->default(true);
            $table->boolean('allow_guest_cancellation_after_deadline')->default(true);
            $table->boolean('allow_guest_relocation_after_deadline')->default(true);
            $table->boolean('guest_friendly_refund_if_confirmed')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('sleeping_place_id', 'host_unresponsive_policies_place_idx');
            $table->index('property_id', 'host_unresponsive_policies_property_idx');
            $table->index('active', 'host_unresponsive_policies_active_idx');
        });

        Schema::create('host_unresponsive_policy_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('pre_check_in_response_minutes');
            $table->unsignedInteger('check_in_response_minutes');
            $table->unsignedInteger('guest_waiting_outside_response_minutes');
            $table->unsignedInteger('night_entry_response_minutes');
            $table->unsignedInteger('urgent_response_minutes');
            $table->boolean('notify_representative_if_available')->default(true);
            $table->boolean('auto_show_instructions_if_allowed')->default(true);
            $table->boolean('auto_block_no_show_while_active')->default(true);
            $table->boolean('allow_guest_cancellation_after_deadline')->default(true);
            $table->boolean('allow_guest_relocation_after_deadline')->default(true);
            $table->boolean('guest_friendly_refund_if_confirmed')->default(true);
            $table->json('policy_snapshot_json')->nullable();
            $table->timestamps();

            $table->index('sleeping_place_id', 'host_unresponsive_snapshots_place_idx');
            $table->index('property_id', 'host_unresponsive_snapshots_property_idx');
        });

        Schema::create('booking_host_unresponsive_cases', function (Blueprint $table): void {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_representative_id')->nullable()->constrained('host_representatives')->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->string('case_type');
            $table->string('reason_key');
            $table->string('status')->default('reported');
            $table->date('check_in_date')->nullable();
            $table->string('planned_check_in_time')->nullable();
            $table->string('check_in_window')->nullable();
            $table->timestamp('actual_guest_arrival_at')->nullable();
            $table->boolean('guest_marked_arrived')->default(false);
            $table->boolean('guest_waiting_outside')->default(false);
            $table->boolean('guest_at_address')->default(false);
            $table->boolean('guest_feels_unsafe')->default(false);
            $table->boolean('instruction_was_available')->default(false);
            $table->boolean('exact_address_was_shown')->default(false);
            $table->boolean('door_code_was_shown')->default(false);
            $table->boolean('intercom_code_was_shown')->default(false);
            $table->boolean('key_safe_code_was_shown')->default(false);
            $table->boolean('host_contact_was_shown')->default(false);
            $table->boolean('representative_contact_was_shown')->default(false);
            $table->unsignedInteger('host_contact_attempts_count')->default(0);
            $table->unsignedInteger('representative_contact_attempts_count')->default(0);
            $table->timestamp('last_host_contact_attempt_at')->nullable();
            $table->timestamp('last_representative_contact_attempt_at')->nullable();
            $table->timestamp('host_last_response_at')->nullable();
            $table->timestamp('representative_last_response_at')->nullable();
            $table->unsignedInteger('response_deadline_minutes')->default(60);
            $table->timestamp('response_deadline_at')->nullable();
            $table->timestamp('response_deadline_expired_at')->nullable();
            $table->boolean('guest_wants_help')->default(true);
            $table->boolean('guest_wants_cancellation')->default(false);
            $table->boolean('guest_wants_refund')->default(false);
            $table->boolean('guest_wants_relocation')->default(false);
            $table->text('host_response')->nullable();
            $table->text('representative_response')->nullable();
            $table->text('guest_comment')->nullable();
            $table->text('host_comment')->nullable();
            $table->string('decision_key')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('refund_status')->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('compensation_amount_future', 12, 2)->default(0);
            $table->string('currency', 3)->nullable();
            $table->foreignId('booking_cancellation_id')->nullable()->constrained('booking_cancellations')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->unsignedBigInteger('complaint_case_id')->nullable();
            $table->unsignedBigInteger('check_in_problem_id')->nullable();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->boolean('future_support_review_required')->default(false);
            $table->text('future_support_comment')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('booking_id', 'host_unresponsive_cases_booking_idx');
            $table->index('booking_check_in_id', 'host_unresponsive_cases_check_in_idx');
            $table->index('booking_stay_id', 'host_unresponsive_cases_stay_idx');
            $table->index(['guest_user_id', 'status'], 'host_unresponsive_cases_guest_status_idx');
            $table->index(['host_user_id', 'status'], 'host_unresponsive_cases_host_status_idx');
            $table->index('host_representative_id', 'host_unresponsive_cases_representative_idx');
            $table->index(['property_id', 'status'], 'host_unresponsive_cases_property_status_idx');
            $table->index(['room_id', 'status'], 'host_unresponsive_cases_room_status_idx');
            $table->index(['sleeping_place_id', 'status'], 'host_unresponsive_cases_place_status_idx');
            $table->index('case_type', 'host_unresponsive_cases_type_idx');
            $table->index('reason_key', 'host_unresponsive_cases_reason_idx');
            $table->index('status', 'host_unresponsive_cases_status_idx');
            $table->index('response_deadline_at', 'host_unresponsive_cases_deadline_idx');
            $table->index('decision_key', 'host_unresponsive_cases_decision_idx');
            $table->index('booking_cancellation_id', 'host_unresponsive_cases_cancellation_idx');
            $table->index('booking_relocation_id', 'host_unresponsive_cases_relocation_idx');
            $table->index('booking_refund_id', 'host_unresponsive_cases_refund_idx');
            $table->index('created_at', 'host_unresponsive_cases_created_idx');
        });

        Schema::create('host_unresponsive_contact_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_unresponsive_case_id')->constrained('booking_host_unresponsive_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_type');
            $table->string('target_name_snapshot')->nullable();
            $table->string('target_contact_snapshot')->nullable();
            $table->string('contact_channel');
            $table->string('attempt_type');
            $table->string('status')->default('created');
            $table->string('message_key')->nullable();
            $table->text('message_text')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamp('response_received_at')->nullable();
            $table->text('response_summary')->nullable();
            $table->timestamps();

            $table->index('host_unresponsive_case_id', 'host_unresponsive_attempts_case_idx');
            $table->index('booking_id', 'host_unresponsive_attempts_booking_idx');
            $table->index('target_type', 'host_unresponsive_attempts_target_type_idx');
            $table->index('target_user_id', 'host_unresponsive_attempts_target_user_idx');
            $table->index('contact_channel', 'host_unresponsive_attempts_channel_idx');
            $table->index('attempt_type', 'host_unresponsive_attempts_type_idx');
            $table->index('status', 'host_unresponsive_attempts_status_idx');
            $table->index('attempted_at', 'host_unresponsive_attempts_at_idx');
        });

        Schema::create('host_unresponsive_guest_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_unresponsive_case_id')->constrained('booking_host_unresponsive_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type');
            $table->text('message')->nullable();
            $table->text('guest_location_note')->nullable();
            $table->timestamp('new_waiting_until')->nullable();
            $table->timestamps();

            $table->index('host_unresponsive_case_id', 'host_unresponsive_guest_actions_case_idx');
            $table->index('booking_id', 'host_unresponsive_guest_actions_booking_idx');
            $table->index('guest_user_id', 'host_unresponsive_guest_actions_guest_idx');
            $table->index('action_type', 'host_unresponsive_guest_actions_type_idx');
            $table->index('created_at', 'host_unresponsive_guest_actions_created_idx');
        });

        Schema::create('host_unresponsive_host_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_unresponsive_case_id')->constrained('booking_host_unresponsive_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->boolean('instruction_resent')->default(false);
            $table->boolean('access_details_provided')->default(false);
            $table->string('new_arrival_time_proposed')->nullable();
            $table->boolean('representative_assigned')->default(false);
            $table->foreignId('alternative_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->timestamps();

            $table->index('host_unresponsive_case_id', 'host_unresponsive_host_responses_case_idx');
            $table->index('booking_id', 'host_unresponsive_host_responses_booking_idx');
            $table->index('host_user_id', 'host_unresponsive_host_responses_host_idx');
            $table->index('response_type', 'host_unresponsive_host_responses_type_idx');
            $table->index('created_at', 'host_unresponsive_host_responses_created_idx');
        });

        Schema::create('host_unresponsive_representative_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_unresponsive_case_id')->constrained('booking_host_unresponsive_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_representative_id')->nullable()->constrained('host_representatives')->nullOnDelete();
            $table->foreignId('representative_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->boolean('will_meet_guest')->default(false);
            $table->string('estimated_arrival_time')->nullable();
            $table->boolean('access_help_provided')->default(false);
            $table->boolean('keys_handed_over')->default(false);
            $table->boolean('guest_checked_in')->default(false);
            $table->timestamps();

            $table->index('host_unresponsive_case_id', 'host_unresponsive_representative_responses_case_idx');
            $table->index('booking_id', 'host_unresponsive_representative_responses_booking_idx');
            $table->index('host_representative_id', 'host_unresponsive_representative_responses_rep_idx');
            $table->index('representative_user_id', 'host_unresponsive_representative_responses_user_idx');
            $table->index('response_type', 'host_unresponsive_representative_responses_type_idx');
            $table->index('created_at', 'host_unresponsive_representative_responses_created_idx');
        });

        Schema::create('host_unresponsive_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_unresponsive_case_id')->constrained('booking_host_unresponsive_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('media_type');
            $table->string('media_role');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host');
            $table->timestamps();

            $table->index('host_unresponsive_case_id', 'host_unresponsive_media_case_idx');
            $table->index('booking_id', 'host_unresponsive_media_booking_idx');
            $table->index('uploaded_by_user_id', 'host_unresponsive_media_user_idx');
            $table->index('media_role', 'host_unresponsive_media_role_idx');
            $table->index('visibility', 'host_unresponsive_media_visibility_idx');
        });

        Schema::create('host_unresponsive_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_unresponsive_case_id')->constrained('booking_host_unresponsive_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('host_unresponsive_case_id', 'host_unresponsive_logs_case_idx');
            $table->index('booking_id', 'host_unresponsive_logs_booking_idx');
            $table->index('new_status', 'host_unresponsive_logs_status_idx');
            $table->index('user_id', 'host_unresponsive_logs_user_idx');
            $table->index('created_at', 'host_unresponsive_logs_created_idx');
        });

        Schema::create('host_unresponsive_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_unresponsive_case_id')->constrained('booking_host_unresponsive_cases')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('host_unresponsive_case_id', 'host_unresponsive_events_case_idx');
            $table->index('booking_id', 'host_unresponsive_events_booking_idx');
            $table->index('event_key', 'host_unresponsive_events_key_idx');
            $table->index('event_type', 'host_unresponsive_events_type_idx');
            $table->index(['source_type', 'source_id'], 'host_unresponsive_events_source_idx');
            $table->index('user_id', 'host_unresponsive_events_user_idx');
            $table->index('occurred_at', 'host_unresponsive_events_occurred_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('host_unresponsive_events');
        Schema::dropIfExists('host_unresponsive_status_logs');
        Schema::dropIfExists('host_unresponsive_media');
        Schema::dropIfExists('host_unresponsive_representative_responses');
        Schema::dropIfExists('host_unresponsive_host_responses');
        Schema::dropIfExists('host_unresponsive_guest_actions');
        Schema::dropIfExists('host_unresponsive_contact_attempts');
        Schema::dropIfExists('booking_host_unresponsive_cases');
        Schema::dropIfExists('host_unresponsive_policy_snapshots');
        Schema::dropIfExists('host_unresponsive_policies');
    }
};
