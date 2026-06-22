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
        Schema::create('inventory_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('category_key')->unique();
            $table->string('name_translation_key');
            $table->string('description_translation_key')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->string('inventory_number')->unique();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('inventory_category_id')->nullable()->index()->constrained('inventory_categories')->nullOnDelete();
            $table->string('item_type')->index();
            $table->string('inventory_scope')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('condition_status')->default('good')->index();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('pcs');
            $table->boolean('is_returnable')->default(false)->index();
            $table->boolean('is_consumable')->default(false)->index();
            $table->boolean('is_fixed_asset')->default(false)->index();
            $table->boolean('is_guest_visible')->default(false)->index();
            $table->boolean('is_required_for_readiness')->default(false)->index();
            $table->boolean('is_promised_in_listing')->default(false)->index();
            $table->string('current_location_type')->default('property')->index();
            $table->text('current_location_note')->nullable();
            $table->string('storage_location')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('barcode')->nullable();
            $table->string('qr_code')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price_amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->decimal('estimated_replacement_cost_amount', 10, 2)->nullable();
            $table->decimal('deposit_deduction_default_amount', 10, 2)->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamp('last_repaired_at')->nullable();
            $table->timestamp('last_issued_at')->nullable();
            $table->timestamp('last_returned_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->timestamp('disposed_at')->nullable();
            $table->text('host_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->timestamps();

            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
        });

        Schema::create('inventory_item_units', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->string('unit_number')->index();
            $table->string('unit_label')->nullable();
            $table->string('status')->default('available')->index();
            $table->string('condition_status')->default('good')->index();
            $table->string('current_location_type')->default('property')->index();
            $table->text('current_location_note')->nullable();
            $table->foreignId('assigned_booking_id')->nullable()->index()->constrained('bookings')->nullOnDelete();
            $table->foreignId('assigned_guest_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('serial_number')->nullable();
            $table->string('barcode')->nullable()->index();
            $table->string('qr_code')->nullable()->index();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_issued_at')->nullable();
            $table->timestamp('last_returned_at')->nullable();
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamp('last_repaired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_inventory_assignments', function (Blueprint $table): void {
            $table->id();
            $table->string('assignment_number')->unique();
            $table->foreignId('booking_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->index()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->index()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->index()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->index()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('guest_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->nullOnDelete();
            $table->string('assignment_type')->index();
            $table->string('status')->default('planned')->index();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('issued_by_type')->nullable();
            $table->boolean('expected_return')->default(false)->index();
            $table->timestamp('expected_return_at')->nullable()->index();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('returned_to_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('returned_condition_status')->nullable();
            $table->string('condition_at_issue')->nullable();
            $table->string('condition_at_return')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->timestamp('guest_confirmed_received_at')->nullable();
            $table->timestamp('host_confirmed_issued_at')->nullable();
            $table->timestamp('guest_confirmed_returned_at')->nullable();
            $table->timestamp('host_confirmed_returned_at')->nullable();
            $table->text('issue_note')->nullable();
            $table->text('return_note')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->string('movement_number')->unique();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('booking_inventory_assignment_id')->nullable()->index()->constrained('booking_inventory_assignments')->nullOnDelete();
            $table->string('from_location_type')->nullable();
            $table->text('from_location_note')->nullable();
            $table->string('to_location_type');
            $table->text('to_location_note')->nullable();
            $table->string('movement_type')->index();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->foreignId('moved_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamp('moved_at')->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_checks', function (Blueprint $table): void {
            $table->id();
            $table->string('inventory_check_number')->unique();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->index()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->index()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('cleaning_task_id')->nullable()->index()->constrained('cleaning_tasks')->nullOnDelete();
            $table->foreignId('inspection_task_id')->nullable()->index()->constrained('inspection_tasks')->nullOnDelete();
            $table->unsignedBigInteger('maintenance_request_id')->nullable()->index();
            $table->unsignedBigInteger('booking_deposit_case_id')->nullable()->index();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('check_type')->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->foreignId('checked_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('checked_by_type')->nullable();
            $table->unsignedInteger('items_expected_count')->default(0);
            $table->unsignedInteger('items_checked_count')->default(0);
            $table->unsignedInteger('items_missing_count')->default(0);
            $table->unsignedInteger('items_damaged_count')->default(0);
            $table->boolean('issues_found')->default(false)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_check_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_check_id')->index()->constrained('inventory_checks')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->nullOnDelete();
            $table->boolean('expected_present')->default(true);
            $table->boolean('is_present')->default(false);
            $table->boolean('expected_return')->default(false);
            $table->boolean('is_returned')->default(false);
            $table->string('expected_condition_status')->nullable();
            $table->string('actual_condition_status')->nullable();
            $table->boolean('missing')->default(false)->index();
            $table->boolean('damaged')->default(false)->index();
            $table->boolean('dirty')->default(false);
            $table->boolean('needs_cleaning')->default(false);
            $table->boolean('needs_washing')->default(false);
            $table->boolean('needs_repair')->default(false)->index();
            $table->boolean('needs_replacement')->default(false)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_issues', function (Blueprint $table): void {
            $table->id();
            $table->string('inventory_issue_number')->unique();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->index()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->index()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->index()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('cleaning_task_id')->nullable()->index()->constrained('cleaning_tasks')->nullOnDelete();
            $table->foreignId('inspection_task_id')->nullable()->index()->constrained('inspection_tasks')->nullOnDelete();
            $table->unsignedBigInteger('maintenance_request_id')->nullable()->index();
            $table->unsignedBigInteger('booking_deposit_case_id')->nullable()->index();
            $table->foreignId('complaint_case_id')->nullable()->index()->constrained('complaint_cases')->nullOnDelete();
            $table->foreignId('reported_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('guest_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('issue_type')->index();
            $table->string('severity')->default('medium')->index();
            $table->string('status')->default('reported')->index();
            $table->text('description')->nullable();
            $table->decimal('quantity_affected', 10, 2)->default(1);
            $table->decimal('replacement_cost_amount', 10, 2)->nullable();
            $table->decimal('deduction_suggested_amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('guest_responsibility_status')->nullable()->index();
            $table->unsignedBigInteger('booking_deposit_deduction_id')->nullable()->index();
            $table->unsignedBigInteger('maintenance_request_created_id')->nullable()->index();
            $table->foreignId('complaint_case_created_id')->nullable()->index()->constrained('complaint_cases')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['host_user_id', 'status']);
            $table->index(['guest_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
        });

        Schema::create('inventory_issue_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_issue_id')->index()->constrained('inventory_issues')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('media_type');
            $table->string('media_role')->index();
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host')->index();
            $table->timestamps();
        });

        Schema::create('inventory_replacements', function (Blueprint $table): void {
            $table->id();
            $table->string('replacement_number')->unique();
            $table->foreignId('old_inventory_item_id')->nullable()->index()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('old_inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->nullOnDelete();
            $table->foreignId('new_inventory_item_id')->nullable()->index()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('new_inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->nullOnDelete();
            $table->foreignId('inventory_issue_id')->nullable()->index()->constrained('inventory_issues')->nullOnDelete();
            $table->unsignedBigInteger('maintenance_request_id')->nullable()->index();
            $table->unsignedBigInteger('booking_deposit_deduction_id')->nullable()->index();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('replacement_reason')->index();
            $table->string('status')->default('planned')->index();
            $table->decimal('replacement_cost_amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('replaced_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_consumable_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->nullable()->index()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('cleaning_task_id')->nullable()->index()->constrained('cleaning_tasks')->nullOnDelete();
            $table->foreignId('inspection_task_id')->nullable()->index()->constrained('inspection_tasks')->nullOnDelete();
            $table->string('usage_type')->index();
            $table->decimal('quantity_used', 10, 2)->default(1);
            $table->string('unit')->default('pcs');
            $table->foreignId('used_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_stock_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('alert_type')->index();
            $table->string('status')->default('active')->index();
            $table->decimal('threshold_quantity', 10, 2)->nullable();
            $table->decimal('current_quantity', 10, 2)->nullable();
            $table->string('message_key')->nullable();
            $table->timestamps();
            $table->timestamp('resolved_at')->nullable();
        });

        Schema::create('inventory_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->nullable()->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->cascadeOnDelete();
            $table->foreignId('inventory_issue_id')->nullable()->index()->constrained('inventory_issues')->cascadeOnDelete();
            $table->foreignId('booking_inventory_assignment_id')->nullable()->index()->constrained('booking_inventory_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->index();
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->nullable()->index()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_unit_id')->nullable()->index()->constrained('inventory_item_units')->cascadeOnDelete();
            $table->foreignId('booking_inventory_assignment_id')->nullable()->index()->constrained('booking_inventory_assignments')->cascadeOnDelete();
            $table->foreignId('inventory_issue_id')->nullable()->index()->constrained('inventory_issues')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('event_key')->index();
            $table->string('event_type')->default('system')->index();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_events');
        Schema::dropIfExists('inventory_status_logs');
        Schema::dropIfExists('inventory_stock_alerts');
        Schema::dropIfExists('inventory_consumable_usages');
        Schema::dropIfExists('inventory_replacements');
        Schema::dropIfExists('inventory_issue_media');
        Schema::dropIfExists('inventory_issues');
        Schema::dropIfExists('inventory_check_items');
        Schema::dropIfExists('inventory_checks');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('booking_inventory_assignments');
        Schema::dropIfExists('inventory_item_units');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_categories');
    }
};
