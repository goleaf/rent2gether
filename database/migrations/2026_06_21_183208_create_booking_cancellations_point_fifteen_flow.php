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
        Schema::create('sleeping_place_cancellation_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->string('policy_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('free_cancellation_until_days_before_check_in')->nullable();
            $table->unsignedInteger('free_cancellation_until_hours_before_check_in')->nullable();
            $table->unsignedInteger('penalty_starts_hours_before_check_in')->nullable();
            $table->boolean('first_night_non_refundable')->default(false);
            $table->boolean('cleaning_fee_refundable_before_check_in')->default(true);
            $table->boolean('service_fee_refundable')->default(false);
            $table->boolean('deposit_always_refundable_before_check_in')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['sleeping_place_id', 'active'], 'sp_cancel_policies_place_active_idx');
            $table->index('policy_type', 'sp_cancel_policies_type_idx');
        });

        Schema::create('sleeping_place_cancellation_policy_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_cancellation_policy_id')
                ->constrained('sleeping_place_cancellation_policies')
                ->cascadeOnDelete();
            $table->string('rule_key');
            $table->string('applies_when');
            $table->decimal('refund_percent', 5, 2)->default(0);
            $table->decimal('fixed_penalty_amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sleeping_place_cancellation_policy_id', 'sp_cancel_rules_policy_idx');
            $table->index('rule_key', 'sp_cancel_rules_key_idx');
            $table->index('applies_when', 'sp_cancel_rules_when_idx');
            $table->index('sort_order', 'sp_cancel_rules_sort_idx');
        });

        Schema::create('booking_cancellation_policy_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->string('policy_type');
            $table->string('title_snapshot');
            $table->text('description_snapshot')->nullable();
            $table->json('rules_snapshot_json')->nullable();
            $table->timestamp('free_cancellation_until')->nullable();
            $table->timestamp('cancellation_penalty_starts_at')->nullable();
            $table->boolean('first_night_non_refundable')->default(false);
            $table->boolean('cleaning_fee_refundable_before_check_in')->default(true);
            $table->boolean('service_fee_refundable')->default(false);
            $table->boolean('deposit_always_refundable_before_check_in')->default(true);
            $table->timestamps();

            $table->index('sleeping_place_id', 'booking_cancel_snapshots_place_idx');
            $table->index('policy_type', 'booking_cancel_snapshots_type_idx');
            $table->index('free_cancellation_until', 'booking_cancel_snapshots_free_idx');
        });

        Schema::create('booking_cancellation_previews', function (Blueprint $table): void {
            $table->id();
            $table->string('preview_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('requested_by_type');
            $table->string('cancellation_type');
            $table->string('reason_key');
            $table->text('comment')->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->timestamp('cancelled_at_preview');
            $table->integer('hours_before_check_in')->nullable();
            $table->integer('nights_before_check_in')->nullable();
            $table->unsignedInteger('nights_used')->default(0);
            $table->unsignedInteger('nights_unused')->default(0);
            $table->decimal('accommodation_amount', 12, 2)->default(0);
            $table->decimal('cleaning_fee_amount', 12, 2)->default(0);
            $table->decimal('service_fee_amount', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('city_fee_amount', 12, 2)->default(0);
            $table->decimal('accommodation_refund_amount', 12, 2)->default(0);
            $table->decimal('cleaning_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('service_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('deposit_refund_amount', 12, 2)->default(0);
            $table->decimal('tax_refund_amount', 12, 2)->default(0);
            $table->decimal('city_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('host_payout_adjustment_amount', 12, 2)->default(0);
            $table->decimal('total_refund_amount', 12, 2)->default(0);
            $table->decimal('total_non_refundable_amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->json('policy_snapshot_json')->nullable();
            $table->json('refund_breakdown_json')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('calculated');
            $table->timestamps();

            $table->index('booking_id', 'booking_cancel_previews_booking_idx');
            $table->index('guest_user_id', 'booking_cancel_previews_guest_idx');
            $table->index('host_user_id', 'booking_cancel_previews_host_idx');
            $table->index('property_id', 'booking_cancel_previews_property_idx');
            $table->index('room_id', 'booking_cancel_previews_room_idx');
            $table->index('sleeping_place_id', 'booking_cancel_previews_place_idx');
            $table->index('requested_by_user_id', 'booking_cancel_previews_user_idx');
            $table->index('requested_by_type', 'booking_cancel_previews_by_type_idx');
            $table->index('cancellation_type', 'booking_cancel_previews_type_idx');
            $table->index('reason_key', 'booking_cancel_previews_reason_idx');
            $table->index('status', 'booking_cancel_previews_status_idx');
            $table->index('expires_at', 'booking_cancel_previews_expires_idx');
        });

        Schema::create('booking_cancellations', function (Blueprint $table): void {
            $table->id();
            $table->string('cancellation_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_cancellation_preview_id')->nullable()->constrained('booking_cancellation_previews')->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancelled_by_type');
            $table->string('cancellation_type');
            $table->string('reason_key');
            $table->text('comment')->nullable();
            $table->string('status')->default('confirmed');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->timestamp('cancelled_at');
            $table->integer('hours_before_check_in')->nullable();
            $table->integer('nights_before_check_in')->nullable();
            $table->unsignedInteger('nights_used')->default(0);
            $table->unsignedInteger('nights_unused')->default(0);
            $table->foreignId('policy_snapshot_id')->nullable()->constrained('booking_cancellation_policy_snapshots')->nullOnDelete();
            $table->decimal('accommodation_amount', 12, 2)->default(0);
            $table->decimal('cleaning_fee_amount', 12, 2)->default(0);
            $table->decimal('service_fee_amount', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('city_fee_amount', 12, 2)->default(0);
            $table->decimal('accommodation_refund_amount', 12, 2)->default(0);
            $table->decimal('cleaning_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('service_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('deposit_refund_amount', 12, 2)->default(0);
            $table->decimal('tax_refund_amount', 12, 2)->default(0);
            $table->decimal('city_fee_refund_amount', 12, 2)->default(0);
            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('host_payout_adjustment_amount', 12, 2)->default(0);
            $table->decimal('total_refund_amount', 12, 2)->default(0);
            $table->decimal('total_non_refundable_amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('refund_status')->default('pending');
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->string('calendar_release_status')->default('pending');
            $table->timestamp('dates_released_at')->nullable();
            $table->boolean('requires_host_response')->default(false);
            $table->boolean('requires_dispute')->default(false);
            $table->unsignedBigInteger('complaint_case_id')->nullable();
            $table->unsignedBigInteger('mismatch_report_id')->nullable();
            $table->unsignedBigInteger('host_unresponsive_case_id')->nullable();
            $table->unsignedBigInteger('no_show_case_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('booking_id', 'booking_cancellations_booking_idx');
            $table->index('booking_cancellation_preview_id', 'booking_cancellations_preview_idx');
            $table->index(['guest_user_id', 'status'], 'booking_cancellations_guest_status_idx');
            $table->index(['host_user_id', 'status'], 'booking_cancellations_host_status_idx');
            $table->index('property_id', 'booking_cancellations_property_idx');
            $table->index('room_id', 'booking_cancellations_room_idx');
            $table->index(['sleeping_place_id', 'status'], 'booking_cancellations_place_status_idx');
            $table->index('cancelled_by_user_id', 'booking_cancellations_cancelled_user_idx');
            $table->index('policy_snapshot_id', 'booking_cancellations_policy_snapshot_idx');
            $table->index('booking_refund_id', 'booking_cancellations_refund_idx');
            $table->index('cancelled_by_type', 'booking_cancellations_by_type_idx');
            $table->index('cancellation_type', 'booking_cancellations_type_idx');
            $table->index('reason_key', 'booking_cancellations_reason_idx');
            $table->index('status', 'booking_cancellations_status_idx');
            $table->index('refund_status', 'booking_cancellations_refund_status_idx');
            $table->index('calendar_release_status', 'booking_cancellations_calendar_status_idx');
            $table->index('created_at', 'booking_cancellations_created_idx');
        });

        Schema::create('booking_cancellation_refund_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_cancellation_id')->constrained('booking_cancellations')->cascadeOnDelete();
            $table->string('line_type');
            $table->string('label_key');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->boolean('refundable')->default(false);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('non_refundable_amount', 12, 2)->default(0);
            $table->string('reason_key')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('booking_cancellation_id', 'booking_cancel_lines_cancellation_idx');
            $table->index('line_type', 'booking_cancel_lines_type_idx');
            $table->index('sort_order', 'booking_cancel_lines_sort_idx');
        });

        Schema::create('booking_cancellation_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_cancellation_id')->constrained('booking_cancellations')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_cancellation_id', 'booking_cancel_logs_cancellation_idx');
            $table->index('booking_id', 'booking_cancel_logs_booking_idx');
            $table->index('new_status', 'booking_cancel_logs_status_idx');
            $table->index('user_id', 'booking_cancel_logs_user_idx');
            $table->index('created_at', 'booking_cancel_logs_created_idx');
        });

        Schema::create('booking_cancellation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_cancellation_id')->constrained('booking_cancellations')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_cancellation_id', 'booking_cancel_events_cancellation_idx');
            $table->index('booking_id', 'booking_cancel_events_booking_idx');
            $table->index('event_key', 'booking_cancel_events_key_idx');
            $table->index('event_type', 'booking_cancel_events_type_idx');
            $table->index(['source_type', 'source_id'], 'booking_cancel_events_source_idx');
            $table->index('user_id', 'booking_cancel_events_user_idx');
            $table->index('occurred_at', 'booking_cancel_events_occurred_idx');
        });

        Schema::create('booking_cancellation_alternatives', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_cancellation_id')->constrained('booking_cancellations')->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('suggestion_type');
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->decimal('price_preview_amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('message_key');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('booking_cancellation_id', 'booking_cancel_alts_cancellation_idx');
            $table->index('sleeping_place_id', 'booking_cancel_alts_place_idx');
            $table->index('property_id', 'booking_cancel_alts_property_idx');
            $table->index('room_id', 'booking_cancel_alts_room_idx');
            $table->index('suggestion_type', 'booking_cancel_alts_type_idx');
            $table->index('sort_order', 'booking_cancel_alts_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_cancellation_alternatives');
        Schema::dropIfExists('booking_cancellation_events');
        Schema::dropIfExists('booking_cancellation_status_logs');
        Schema::dropIfExists('booking_cancellation_refund_lines');
        Schema::dropIfExists('booking_cancellations');
        Schema::dropIfExists('booking_cancellation_previews');
        Schema::dropIfExists('booking_cancellation_policy_snapshots');
        Schema::dropIfExists('sleeping_place_cancellation_policy_rules');
        Schema::dropIfExists('sleeping_place_cancellation_policies');
    }
};
