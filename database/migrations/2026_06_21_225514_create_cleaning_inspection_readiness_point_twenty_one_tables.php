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
        Schema::create('cleaning_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained('sleeping_places')->cascadeOnDelete();
            $table->boolean('cleaning_required_after_checkout')->default(true);
            $table->boolean('cleaning_required_before_checkin')->default(false);
            $table->boolean('inspection_required_after_cleaning')->default(false);
            $table->unsignedInteger('default_cleaning_duration_minutes')->default(120);
            $table->unsignedInteger('default_inspection_duration_minutes')->default(30);
            $table->unsignedInteger('same_day_turnover_min_gap_minutes')->default(180);
            $table->boolean('require_before_photos')->default(false);
            $table->boolean('require_after_photos')->default(true);
            $table->boolean('require_checklist_completion')->default(true);
            $table->boolean('require_host_confirmation')->default(false);
            $table->boolean('auto_create_after_checkout')->default(true);
            $table->boolean('auto_create_before_checkin')->default(false);
            $table->boolean('auto_create_after_complaint')->default(true);
            $table->boolean('auto_create_after_repair')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('property_id');
            $table->index('room_id');
            $table->index('sleeping_place_id');
            $table->index('active');
        });

        Schema::create('cleaning_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('cleaning_number')->unique();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('complaint_case_id')->nullable()->constrained('complaint_cases')->nullOnDelete();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->foreignId('mismatch_report_id')->nullable()->constrained('booking_listing_mismatch_reports')->nullOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->string('cleaning_type');
            $table->string('cleaning_scope');
            $table->string('status')->default('scheduled');
            $table->string('priority')->default('normal');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('scheduled_start_at')->nullable();
            $table->timestamp('scheduled_end_at')->nullable();
            $table->timestamp('actual_started_at')->nullable();
            $table->timestamp('actual_completed_at')->nullable();
            $table->string('responsible_type')->default('not_assigned');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('responsible_name_snapshot')->nullable();
            $table->string('responsible_contact_snapshot')->nullable();
            $table->boolean('access_required')->default(false);
            $table->boolean('access_confirmed')->default(false);
            $table->text('access_instruction_snapshot')->nullable();
            $table->boolean('supplies_required')->default(false);
            $table->text('supplies_note')->nullable();
            $table->boolean('checklist_completed')->default(false);
            $table->boolean('before_photos_required')->default(false);
            $table->boolean('after_photos_required')->default(true);
            $table->boolean('before_photos_uploaded')->default(false);
            $table->boolean('after_photos_uploaded')->default(false);
            $table->boolean('issues_found')->default(false);
            $table->boolean('damage_found')->default(false);
            $table->boolean('extra_dirt_found')->default(false);
            $table->boolean('forgotten_items_found')->default(false);
            $table->boolean('inventory_issue_found')->default(false);
            $table->boolean('repair_required')->default(false);
            $table->boolean('inspection_required')->default(false);
            $table->boolean('deposit_review_required')->default(false);
            $table->text('responsible_comment')->nullable();
            $table->text('host_comment')->nullable();
            $table->text('internal_host_note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('booking_stay_id');
            $table->index('booking_check_in_id');
            $table->index('booking_check_out_id');
            $table->index('booking_relocation_id');
            $table->index('complaint_case_id');
            $table->index('maintenance_request_id');
            $table->index('mismatch_report_id');
            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index('cleaning_type');
            $table->index('cleaning_scope');
            $table->index('status');
            $table->index('priority');
            $table->index('scheduled_start_at');
            $table->index('actual_completed_at');
            $table->index('responsible_user_id');
            $table->index('issues_found');
            $table->index('repair_required');
            $table->index('inspection_required');
        });

        Schema::create('cleaning_task_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_task_id')->constrained('cleaning_tasks')->cascadeOnDelete();
            $table->string('item_key');
            $table->string('label_key');
            $table->string('status')->default('pending');
            $table->boolean('required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['cleaning_task_id', 'item_key']);
            $table->index(['cleaning_task_id', 'status']);
            $table->index('required');
            $table->index('sort_order');
            $table->index('completed_by_user_id');
        });

        Schema::create('cleaning_task_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_task_id')->constrained('cleaning_tasks')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('media_type');
            $table->string('media_role');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('host_only');
            $table->timestamps();

            $table->index('cleaning_task_id');
            $table->index('booking_id');
            $table->index('uploaded_by_user_id');
            $table->index('media_role');
            $table->index('visibility');
        });

        Schema::create('cleaning_task_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_task_id')->constrained('cleaning_tasks')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->string('issue_type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('reported');
            $table->text('description')->nullable();
            $table->boolean('creates_maintenance_request')->default(false);
            $table->boolean('creates_deposit_review')->default(false);
            $table->boolean('creates_complaint')->default(false);
            $table->boolean('blocks_calendar')->default(false);
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->unsignedBigInteger('booking_deposit_case_id')->nullable();
            $table->foreignId('complaint_case_id')->nullable()->constrained('complaint_cases')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('cleaning_task_id');
            $table->index('booking_id');
            $table->index('host_user_id');
            $table->index('property_id');
            $table->index('room_id');
            $table->index('sleeping_place_id');
            $table->index('issue_type');
            $table->index('severity');
            $table->index('status');
            $table->index('blocks_calendar');
            $table->index('maintenance_request_id');
            $table->index('booking_deposit_case_id');
            $table->index('complaint_case_id');
        });

        Schema::create('inspection_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('inspection_number')->unique();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('cleaning_task_id')->nullable()->constrained('cleaning_tasks')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('complaint_case_id')->nullable()->constrained('complaint_cases')->nullOnDelete();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->foreignId('mismatch_report_id')->nullable()->constrained('booking_listing_mismatch_reports')->nullOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->string('inspection_type');
            $table->string('inspection_scope');
            $table->string('status')->default('scheduled');
            $table->string('priority')->default('normal');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('actual_started_at')->nullable();
            $table->timestamp('actual_completed_at')->nullable();
            $table->string('responsible_type')->default('not_assigned');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('responsible_name_snapshot')->nullable();
            $table->string('responsible_contact_snapshot')->nullable();
            $table->boolean('checklist_completed')->default(false);
            $table->boolean('photos_required')->default(false);
            $table->boolean('photos_uploaded')->default(false);
            $table->boolean('passed')->default(false);
            $table->boolean('issues_found')->default(false);
            $table->boolean('cleaning_required')->default(false);
            $table->boolean('repair_required')->default(false);
            $table->boolean('deposit_review_required')->default(false);
            $table->boolean('calendar_block_required')->default(false);
            $table->text('result_summary')->nullable();
            $table->text('responsible_comment')->nullable();
            $table->text('host_comment')->nullable();
            $table->text('internal_host_note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('booking_stay_id');
            $table->index('booking_check_in_id');
            $table->index('booking_check_out_id');
            $table->index('cleaning_task_id');
            $table->index('booking_relocation_id');
            $table->index('complaint_case_id');
            $table->index('maintenance_request_id');
            $table->index('mismatch_report_id');
            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index('inspection_type');
            $table->index('inspection_scope');
            $table->index('status');
            $table->index('priority');
            $table->index('scheduled_at');
            $table->index('actual_completed_at');
            $table->index('responsible_user_id');
            $table->index('issues_found');
            $table->index('repair_required');
            $table->index('cleaning_required');
        });

        Schema::create('inspection_task_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inspection_task_id')->constrained('inspection_tasks')->cascadeOnDelete();
            $table->string('item_key');
            $table->string('label_key');
            $table->string('status')->default('pending');
            $table->boolean('required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('result_value')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['inspection_task_id', 'item_key']);
            $table->index(['inspection_task_id', 'status']);
            $table->index('required');
            $table->index('sort_order');
            $table->index('completed_by_user_id');
        });

        Schema::create('inspection_task_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inspection_task_id')->constrained('inspection_tasks')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('media_type');
            $table->string('media_role');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('host_only');
            $table->timestamps();

            $table->index('inspection_task_id');
            $table->index('booking_id');
            $table->index('uploaded_by_user_id');
            $table->index('media_role');
            $table->index('visibility');
        });

        Schema::create('place_readiness_checks', function (Blueprint $table): void {
            $table->id();
            $table->string('readiness_number')->unique();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('next_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('checking');
            $table->string('check_reason');
            $table->timestamp('target_check_in_at')->nullable();
            $table->boolean('checkout_completed')->default(false);
            $table->boolean('cleaning_completed')->default(false);
            $table->boolean('inspection_completed')->default(false);
            $table->boolean('repair_completed')->default(false);
            $table->boolean('inventory_ready')->default(false);
            $table->boolean('access_ready')->default(false);
            $table->boolean('deposit_review_not_blocking')->default(true);
            $table->boolean('complaint_not_blocking')->default(true);
            $table->boolean('calendar_available')->default(false);
            $table->boolean('same_day_turnover')->default(false);
            $table->unsignedInteger('turnover_gap_minutes')->nullable();
            $table->unsignedInteger('required_gap_minutes')->nullable();
            $table->boolean('gap_is_enough')->default(true);
            $table->string('blocking_reason_key')->nullable();
            $table->text('blocking_reason_text')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('next_booking_id');
            $table->index('property_id');
            $table->index('room_id');
            $table->index('sleeping_place_id');
            $table->index(['host_user_id', 'status']);
            $table->index('status');
            $table->index('check_reason');
            $table->index('target_check_in_at');
            $table->index('same_day_turnover');
            $table->index('calendar_available');
            $table->index('ready_at');
        });

        Schema::create('cleaning_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_task_id')->constrained('cleaning_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('cleaning_task_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('cleaning_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_task_id')->constrained('cleaning_tasks')->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('cleaning_task_id');
            $table->index('event_key');
            $table->index('event_type');
            $table->index(['source_type', 'source_id']);
            $table->index('user_id');
            $table->index('occurred_at');
        });

        Schema::create('inspection_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inspection_task_id')->constrained('inspection_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('inspection_task_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('inspection_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inspection_task_id')->constrained('inspection_tasks')->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('inspection_task_id');
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
        Schema::dropIfExists('inspection_events');
        Schema::dropIfExists('inspection_status_logs');
        Schema::dropIfExists('cleaning_events');
        Schema::dropIfExists('cleaning_status_logs');
        Schema::dropIfExists('place_readiness_checks');
        Schema::dropIfExists('inspection_task_media');
        Schema::dropIfExists('inspection_task_items');
        Schema::dropIfExists('inspection_tasks');
        Schema::dropIfExists('cleaning_task_issues');
        Schema::dropIfExists('cleaning_task_media');
        Schema::dropIfExists('cleaning_task_items');
        Schema::dropIfExists('cleaning_tasks');
        Schema::dropIfExists('cleaning_policies');
    }
};
