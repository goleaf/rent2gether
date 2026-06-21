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
        Schema::create('booking_listing_mismatch_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('mismatch_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('mismatch_type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('reported');
            $table->timestamp('reported_at');
            $table->timestamp('discovered_at')->nullable();
            $table->text('guest_description')->nullable();
            $table->text('host_response')->nullable();
            $table->text('what_was_promised')->nullable();
            $table->text('what_was_actual')->nullable();
            $table->boolean('guest_wants_to_stay')->default(false);
            $table->boolean('guest_wants_fix')->default(true);
            $table->boolean('guest_wants_relocation')->default(false);
            $table->boolean('guest_wants_cancellation')->default(false);
            $table->boolean('guest_wants_refund')->default(false);
            $table->boolean('guest_wants_compensation')->default(false);
            $table->boolean('host_accepts_problem')->nullable();
            $table->boolean('host_offered_fix')->default(false);
            $table->boolean('host_offered_relocation')->default(false);
            $table->boolean('host_offered_refund')->default(false);
            $table->boolean('host_offered_compensation')->default(false);
            $table->boolean('host_denied_problem')->default(false);
            $table->string('resolution_type')->nullable();
            $table->string('resolution_status')->nullable();
            $table->decimal('compensation_amount', 12, 2)->default(0);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('price_difference_amount', 12, 2)->default(0);
            $table->string('currency', 3)->nullable();
            $table->unsignedBigInteger('cleaning_task_id')->nullable();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('booking_cancellation_id')->nullable()->constrained('booking_cancellations')->nullOnDelete();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->unsignedBigInteger('complaint_case_id')->nullable();
            $table->boolean('snapshot_compared')->default(false);
            $table->decimal('auto_match_confidence', 5, 2)->nullable();
            $table->boolean('future_review_required')->default(false);
            $table->text('future_review_comment')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('booking_stay_id');
            $table->index('booking_check_in_id');
            $table->index('booking_check_out_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index(['source_type', 'source_id']);
            $table->index('mismatch_type');
            $table->index('severity');
            $table->index('status');
            $table->index('resolution_type');
            $table->index('cleaning_task_id');
            $table->index('maintenance_request_id');
            $table->index('booking_relocation_id');
            $table->index('booking_cancellation_id');
            $table->index('booking_refund_id');
            $table->index('complaint_case_id');
            $table->index('created_at');
        });

        Schema::create('booking_listing_mismatch_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->string('item_key');
            $table->string('item_type');
            $table->text('promised_value')->nullable();
            $table->text('actual_value')->nullable();
            $table->string('snapshot_source_type')->nullable();
            $table->unsignedBigInteger('snapshot_source_id')->nullable();
            $table->boolean('is_confirmed')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('severity')->default('medium');
            $table->text('guest_note')->nullable();
            $table->text('host_note')->nullable();
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('item_key');
            $table->index('item_type');
            $table->index('is_confirmed');
            $table->index('severity');
        });

        Schema::create('booking_listing_mismatch_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('media_type');
            $table->string('media_role');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host');
            $table->foreignId('related_mismatch_item_id')->nullable()->constrained('booking_listing_mismatch_items')->nullOnDelete();
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('booking_id');
            $table->index('uploaded_by_user_id');
            $table->index('media_role');
            $table->index('visibility');
            $table->index('related_mismatch_item_id');
        });

        Schema::create('booking_listing_mismatch_host_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->boolean('accepts_problem')->nullable();
            $table->string('proposed_resolution_type')->nullable();
            $table->decimal('offered_compensation_amount', 12, 2)->nullable();
            $table->decimal('offered_refund_amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->foreignId('alternative_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->unsignedBigInteger('cleaning_task_id')->nullable();
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('host_user_id');
            $table->index('response_type');
            $table->index('proposed_resolution_type');
            $table->index('alternative_sleeping_place_id');
            $table->index('maintenance_request_id');
            $table->index('cleaning_task_id');
        });

        Schema::create('booking_listing_mismatch_guest_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->string('accepted_resolution_type')->nullable();
            $table->decimal('accepted_compensation_amount', 12, 2)->nullable();
            $table->decimal('accepted_refund_amount', 12, 2)->nullable();
            $table->foreignId('accepted_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('guest_user_id');
            $table->index('response_type');
            $table->index('accepted_resolution_type');
            $table->index('accepted_relocation_id');
        });

        Schema::create('booking_listing_mismatch_resolution_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->string('resolution_type');
            $table->string('status')->default('offered');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->foreignId('sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('booking_cancellation_id')->nullable()->constrained('booking_cancellations')->nullOnDelete();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->unsignedBigInteger('cleaning_task_id')->nullable();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->foreignId('offered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('resolution_type');
            $table->index('status');
            $table->index('sleeping_place_id');
            $table->index('booking_relocation_id');
            $table->index('booking_cancellation_id');
            $table->index('booking_refund_id');
            $table->index('cleaning_task_id');
            $table->index('maintenance_request_id');
            $table->index('offered_by_user_id');
            $table->index('accepted_by_user_id');
        });

        Schema::create('booking_listing_mismatch_compensation_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->string('line_type');
            $table->string('label_key');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('calculation_type')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->unsignedInteger('nights_count')->nullable();
            $table->boolean('refundable')->default(true);
            $table->boolean('payable_to_guest')->default(true);
            $table->boolean('deduct_from_host_payout')->default(false);
            $table->string('reason_key')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('line_type');
            $table->index('payable_to_guest');
            $table->index('deduct_from_host_payout');
            $table->index('sort_order');
        });

        Schema::create('booking_listing_mismatch_warnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->string('warning_key');
            $table->string('severity')->default('warning');
            $table->string('message_key');
            $table->text('message_params_json')->nullable();
            $table->boolean('visible_to_guest')->default(true);
            $table->boolean('visible_to_host')->default(true);
            $table->boolean('blocking')->default(false);
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('warning_key');
            $table->index('severity');
            $table->index('visible_to_guest');
            $table->index('visible_to_host');
            $table->index('blocking');
        });

        Schema::create('booking_listing_mismatch_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('booking_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('booking_listing_mismatch_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_listing_mismatch_report_id')->constrained('booking_listing_mismatch_reports')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_listing_mismatch_report_id');
            $table->index('booking_id');
            $table->index('event_key');
            $table->index('event_type');
            $table->index(['source_type', 'source_id']);
            $table->index('user_id');
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_listing_mismatch_events');
        Schema::dropIfExists('booking_listing_mismatch_status_logs');
        Schema::dropIfExists('booking_listing_mismatch_warnings');
        Schema::dropIfExists('booking_listing_mismatch_compensation_lines');
        Schema::dropIfExists('booking_listing_mismatch_resolution_options');
        Schema::dropIfExists('booking_listing_mismatch_guest_responses');
        Schema::dropIfExists('booking_listing_mismatch_host_responses');
        Schema::dropIfExists('booking_listing_mismatch_media');
        Schema::dropIfExists('booking_listing_mismatch_items');
        Schema::dropIfExists('booking_listing_mismatch_reports');
    }
};
