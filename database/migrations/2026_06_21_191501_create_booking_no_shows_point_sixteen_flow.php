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
        Schema::create('booking_no_show_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->unsignedInteger('waiting_period_minutes')->default(180);
            $table->unsignedInteger('same_day_waiting_period_minutes')->nullable();
            $table->unsignedInteger('night_arrival_waiting_period_minutes')->nullable();
            $table->boolean('hold_first_night_on_no_show')->default(true);
            $table->boolean('release_remaining_nights_after_no_show')->default(true);
            $table->boolean('refund_deposit_on_no_show')->default(true);
            $table->boolean('refund_cleaning_fee_on_no_show')->default(true);
            $table->boolean('refund_service_fee_on_no_show')->default(false);
            $table->string('host_payout_rule')->default('policy_based');
            $table->string('guest_penalty_rule')->default('policy_based');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['sleeping_place_id', 'active'], 'booking_no_show_policies_place_active_idx');
            $table->index('host_payout_rule', 'booking_no_show_policies_host_payout_idx');
            $table->index('guest_penalty_rule', 'booking_no_show_policies_guest_penalty_idx');
        });

        Schema::create('booking_no_show_policy_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->unsignedInteger('waiting_period_minutes');
            $table->unsignedInteger('same_day_waiting_period_minutes')->nullable();
            $table->unsignedInteger('night_arrival_waiting_period_minutes')->nullable();
            $table->boolean('hold_first_night_on_no_show')->default(true);
            $table->boolean('release_remaining_nights_after_no_show')->default(true);
            $table->boolean('refund_deposit_on_no_show')->default(true);
            $table->boolean('refund_cleaning_fee_on_no_show')->default(true);
            $table->boolean('refund_service_fee_on_no_show')->default(false);
            $table->string('host_payout_rule');
            $table->string('guest_penalty_rule');
            $table->json('policy_snapshot_json')->nullable();
            $table->timestamps();

            $table->index('sleeping_place_id', 'booking_no_show_snapshots_place_idx');
        });

        Schema::create('booking_no_shows', function (Blueprint $table): void {
            $table->id();
            $table->string('no_show_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->string('status')->default('watching');
            $table->string('reason_key')->nullable();
            $table->date('check_in_date');
            $table->string('planned_check_in_time')->nullable();
            $table->string('check_in_window')->nullable();
            $table->timestamp('no_show_started_at')->nullable();
            $table->timestamp('host_reported_at')->nullable();
            $table->timestamp('guest_contacted_at')->nullable();
            $table->timestamp('guest_last_response_at')->nullable();
            $table->unsignedInteger('waiting_period_minutes')->default(180);
            $table->timestamp('waiting_until')->nullable();
            $table->timestamp('waiting_expired_at')->nullable();
            $table->boolean('guest_not_answering')->default(false);
            $table->boolean('guest_warned_late_arrival')->default(false);
            $table->boolean('guest_warned_cancellation')->default(false);
            $table->boolean('guest_claimed_arrived')->default(false);
            $table->boolean('host_marked_no_show')->default(false);
            $table->string('guest_response_type')->nullable();
            $table->text('guest_response_message')->nullable();
            $table->text('host_comment')->nullable();
            $table->text('guest_comment')->nullable();
            $table->string('decision_key')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('refund_or_penalty_status')->default('not_calculated');
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('deposit_refund_amount', 12, 2)->default(0);
            $table->decimal('cleaning_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('service_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('host_payout_amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('calendar_release_status')->default('not_released');
            $table->timestamp('dates_released_at')->nullable();
            $table->foreignId('booking_cancellation_id')->nullable()->constrained('booking_cancellations')->nullOnDelete();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->unsignedBigInteger('complaint_case_id')->nullable();
            $table->unsignedBigInteger('host_unresponsive_case_id')->nullable();
            $table->boolean('future_support_review_required')->default(false);
            $table->text('future_support_comment')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('booking_id', 'booking_no_shows_booking_idx');
            $table->index('booking_check_in_id', 'booking_no_shows_check_in_idx');
            $table->index(['guest_user_id', 'status'], 'booking_no_shows_guest_status_idx');
            $table->index(['host_user_id', 'status'], 'booking_no_shows_host_status_idx');
            $table->index(['property_id', 'status'], 'booking_no_shows_property_status_idx');
            $table->index(['room_id', 'status'], 'booking_no_shows_room_status_idx');
            $table->index(['sleeping_place_id', 'status'], 'booking_no_shows_place_status_idx');
            $table->index('decided_by_user_id', 'booking_no_shows_decider_idx');
            $table->index('booking_cancellation_id', 'booking_no_shows_cancellation_idx');
            $table->index('booking_refund_id', 'booking_no_shows_refund_idx');
            $table->index('check_in_date', 'booking_no_shows_check_in_date_idx');
            $table->index('status', 'booking_no_shows_status_idx');
            $table->index('reason_key', 'booking_no_shows_reason_idx');
            $table->index('waiting_until', 'booking_no_shows_waiting_until_idx');
            $table->index('decision_key', 'booking_no_shows_decision_idx');
            $table->index('refund_or_penalty_status', 'booking_no_shows_money_status_idx');
            $table->index('calendar_release_status', 'booking_no_shows_calendar_status_idx');
            $table->index('created_at', 'booking_no_shows_created_idx');
        });

        Schema::create('booking_no_show_contact_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_no_show_id')->constrained('booking_no_shows')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attempted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contact_channel');
            $table->string('attempt_type');
            $table->string('status')->default('created');
            $table->string('message_key')->nullable();
            $table->text('message_text')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamp('response_received_at')->nullable();
            $table->text('response_summary')->nullable();
            $table->timestamps();

            $table->index('booking_no_show_id', 'booking_no_show_attempts_case_idx');
            $table->index('booking_id', 'booking_no_show_attempts_booking_idx');
            $table->index('attempted_by_user_id', 'booking_no_show_attempts_user_idx');
            $table->index('contact_channel', 'booking_no_show_attempts_channel_idx');
            $table->index('attempt_type', 'booking_no_show_attempts_type_idx');
            $table->index('status', 'booking_no_show_attempts_status_idx');
            $table->index('attempted_at', 'booking_no_show_attempts_at_idx');
        });

        Schema::create('booking_no_show_guest_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_no_show_id')->constrained('booking_no_shows')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->string('new_arrival_time')->nullable();
            $table->unsignedBigInteger('evidence_media_id')->nullable();
            $table->timestamps();

            $table->index('booking_no_show_id', 'booking_no_show_responses_case_idx');
            $table->index('booking_id', 'booking_no_show_responses_booking_idx');
            $table->index('guest_user_id', 'booking_no_show_responses_guest_idx');
            $table->index('response_type', 'booking_no_show_responses_type_idx');
            $table->index('created_at', 'booking_no_show_responses_created_idx');
        });

        Schema::create('booking_no_show_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_no_show_id')->constrained('booking_no_shows')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('media_type');
            $table->string('media_role');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host');
            $table->timestamps();

            $table->index('booking_no_show_id', 'booking_no_show_media_case_idx');
            $table->index('booking_id', 'booking_no_show_media_booking_idx');
            $table->index('uploaded_by_user_id', 'booking_no_show_media_user_idx');
            $table->index('media_role', 'booking_no_show_media_role_idx');
            $table->index('visibility', 'booking_no_show_media_visibility_idx');
        });

        Schema::create('booking_no_show_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_no_show_id')->constrained('booking_no_shows')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_no_show_id', 'booking_no_show_logs_case_idx');
            $table->index('booking_id', 'booking_no_show_logs_booking_idx');
            $table->index('new_status', 'booking_no_show_logs_status_idx');
            $table->index('user_id', 'booking_no_show_logs_user_idx');
            $table->index('created_at', 'booking_no_show_logs_created_idx');
        });

        Schema::create('booking_no_show_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_no_show_id')->constrained('booking_no_shows')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_no_show_id', 'booking_no_show_events_case_idx');
            $table->index('booking_id', 'booking_no_show_events_booking_idx');
            $table->index('event_key', 'booking_no_show_events_key_idx');
            $table->index('event_type', 'booking_no_show_events_type_idx');
            $table->index(['source_type', 'source_id'], 'booking_no_show_events_source_idx');
            $table->index('user_id', 'booking_no_show_events_user_idx');
            $table->index('occurred_at', 'booking_no_show_events_occurred_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_no_show_events');
        Schema::dropIfExists('booking_no_show_status_logs');
        Schema::dropIfExists('booking_no_show_media');
        Schema::dropIfExists('booking_no_show_guest_responses');
        Schema::dropIfExists('booking_no_show_contact_attempts');
        Schema::dropIfExists('booking_no_shows');
        Schema::dropIfExists('booking_no_show_policy_snapshots');
        Schema::dropIfExists('booking_no_show_policies');
    }
};
