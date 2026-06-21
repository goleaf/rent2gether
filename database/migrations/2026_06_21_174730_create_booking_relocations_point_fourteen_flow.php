<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createBookingRelocations();
        $this->createBookingRelocationOptions();
        $this->createBookingRelocationPriceLines();
        $this->createBookingRelocationValidationResults();
        $this->createBookingRelocationConsents();
        $this->createBookingRelocationHostResponses();
        $this->createBookingRelocationGuestResponses();
        $this->createBookingRelocationInventoryTransfers();
        $this->createBookingRelocationStatusLogs();
        $this->createBookingRelocationEvents();
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_relocation_events');
        Schema::dropIfExists('booking_relocation_status_logs');
        Schema::dropIfExists('booking_relocation_inventory_transfers');
        Schema::dropIfExists('booking_relocation_guest_responses');
        Schema::dropIfExists('booking_relocation_host_responses');
        Schema::dropIfExists('booking_relocation_consents');
        Schema::dropIfExists('booking_relocation_validation_results');
        Schema::dropIfExists('booking_relocation_price_lines');
        Schema::dropIfExists('booking_relocation_options');
        Schema::dropIfExists('booking_relocations');
    }

    private function createBookingRelocations(): void
    {
        Schema::create('booking_relocations', function (Blueprint $table): void {
            $table->id();
            $table->string('relocation_number')->unique();
            $table->foreignId('original_booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('new_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('current_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('current_sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->foreignId('new_property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('new_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('new_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requested_by_type')->default('guest');
            $table->string('reason');
            $table->string('status')->default('requested');
            $table->date('relocation_date');
            $table->string('relocation_time')->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->date('original_check_in_date');
            $table->date('original_check_out_date');
            $table->date('old_period_check_in_date');
            $table->date('old_period_check_out_date');
            $table->date('new_period_check_in_date');
            $table->date('new_period_check_out_date');
            $table->decimal('old_remaining_value_amount', 12, 2)->default(0);
            $table->decimal('new_remaining_value_amount', 12, 2)->default(0);
            $table->decimal('price_difference_amount', 12, 2)->default(0);
            $table->decimal('additional_payment_amount', 12, 2)->default(0);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('additional_deposit_amount', 12, 2)->default(0);
            $table->decimal('cleaning_fee_difference_amount', 12, 2)->default(0);
            $table->decimal('service_fee_difference_amount', 12, 2)->default(0);
            $table->decimal('host_payout_difference_amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('price_difference_payer')->default('guest');
            $table->boolean('requires_guest_consent')->default(true);
            $table->boolean('requires_host_consent')->default(true);
            $table->timestamp('guest_consented_at')->nullable();
            $table->timestamp('host_consented_at')->nullable();
            $table->boolean('requires_payment')->default(false);
            $table->string('payment_status')->default('not_required');
            $table->foreignId('booking_payment_id')->nullable()->constrained('booking_payments')->nullOnDelete();
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('payment_deadline_at')->nullable();
            $table->boolean('requires_refund')->default(false);
            $table->string('refund_status')->nullable();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->nullOnDelete();
            $table->text('guest_comment')->nullable();
            $table->text('host_comment')->nullable();
            $table->text('support_comment')->nullable();
            $table->string('future_support_status')->nullable();
            $table->string('future_support_decision')->nullable();
            $table->boolean('hold_dates')->default(true);
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['original_booking_id', 'status'], 'booking_relocations_original_status_idx');
            $table->index('new_booking_id', 'booking_relocations_new_booking_idx');
            $table->index('booking_stay_id', 'booking_relocations_stay_idx');
            $table->index(['guest_user_id', 'status'], 'booking_relocations_guest_status_idx');
            $table->index(['host_user_id', 'status'], 'booking_relocations_host_status_idx');
            $table->index('current_property_id', 'booking_relocations_current_property_idx');
            $table->index('current_room_id', 'booking_relocations_current_room_idx');
            $table->index(['current_sleeping_place_id', 'status'], 'booking_relocations_current_place_status_idx');
            $table->index('new_property_id', 'booking_relocations_new_property_idx');
            $table->index('new_room_id', 'booking_relocations_new_room_idx');
            $table->index(['new_sleeping_place_id', 'status'], 'booking_relocations_new_place_status_idx');
            $table->index('requested_by_user_id', 'booking_relocations_requested_by_idx');
            $table->index('relocation_date', 'booking_relocations_date_idx');
            $table->index('status', 'booking_relocations_status_idx');
            $table->index('reason', 'booking_relocations_reason_idx');
            $table->index(['source_type', 'source_id'], 'booking_relocations_source_idx');
            $table->index('payment_status', 'booking_relocations_payment_status_idx');
            $table->index('refund_status', 'booking_relocations_refund_status_idx');
            $table->index('booking_payment_id', 'booking_relocations_payment_idx');
            $table->index('booking_refund_id', 'booking_relocations_refund_idx');
            $table->index('expires_at', 'booking_relocations_expires_idx');
            $table->index('hold_expires_at', 'booking_relocations_hold_expires_idx');
            $table->index('applied_at', 'booking_relocations_applied_idx');
        });
    }

    private function createBookingRelocationOptions(): void
    {
        Schema::create('booking_relocation_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->decimal('price_difference_amount', 12, 2)->default(0);
            $table->decimal('additional_payment_amount', 12, 2)->default(0);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('additional_deposit_amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('availability_status')->default('unchecked');
            $table->string('compatibility_status')->default('unchecked');
            $table->string('pricing_status')->default('unchecked');
            $table->string('distance_label')->nullable();
            $table->string('room_privacy_level')->nullable();
            $table->decimal('comfort_score', 6, 2)->nullable();
            $table->decimal('match_score', 6, 2)->nullable();
            $table->text('host_note')->nullable();
            $table->boolean('guest_selected')->default(false);
            $table->timestamp('selected_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('booking_relocation_id', 'booking_relocation_options_relocation_idx');
            $table->index('sleeping_place_id', 'booking_relocation_options_place_idx');
            $table->index('property_id', 'booking_relocation_options_property_idx');
            $table->index('room_id', 'booking_relocation_options_room_idx');
            $table->index('availability_status', 'booking_relocation_options_availability_idx');
            $table->index('compatibility_status', 'booking_relocation_options_compatibility_idx');
            $table->index('pricing_status', 'booking_relocation_options_pricing_idx');
            $table->index('guest_selected', 'booking_relocation_options_selected_idx');
            $table->index('match_score', 'booking_relocation_options_match_idx');
        });
    }

    private function createBookingRelocationPriceLines(): void
    {
        Schema::create('booking_relocation_price_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->string('line_type');
            $table->string('label_key');
            $table->date('date')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_fee')->default(false);
            $table->boolean('is_deposit')->default(false);
            $table->boolean('is_refundable')->default(true);
            $table->boolean('is_payable_now')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['booking_relocation_id', 'line_type'], 'booking_relocation_lines_relocation_type_idx');
            $table->index(['booking_relocation_id', 'sort_order'], 'booking_relocation_lines_relocation_sort_idx');
            $table->index('date', 'booking_relocation_lines_date_idx');
            $table->index('is_deposit', 'booking_relocation_lines_deposit_idx');
            $table->index('is_refundable', 'booking_relocation_lines_refundable_idx');
        });
    }

    private function createBookingRelocationValidationResults(): void
    {
        Schema::create('booking_relocation_validation_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->string('validation_key');
            $table->string('severity')->default('error');
            $table->string('message_key');
            $table->text('message_params_json')->nullable();
            $table->boolean('blocking')->default(true);
            $table->boolean('visible_to_guest')->default(true);
            $table->boolean('visible_to_host')->default(true);
            $table->timestamps();

            $table->index('booking_relocation_id', 'booking_relocation_validation_relocation_idx');
            $table->index('validation_key', 'booking_relocation_validation_key_idx');
            $table->index('blocking', 'booking_relocation_validation_blocking_idx');
            $table->index('severity', 'booking_relocation_validation_severity_idx');
            $table->index('visible_to_guest', 'booking_relocation_validation_guest_idx');
            $table->index('visible_to_host', 'booking_relocation_validation_host_idx');
        });
    }

    private function createBookingRelocationConsents(): void
    {
        Schema::create('booking_relocation_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('consent_type');
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['booking_relocation_id', 'consent_type'], 'booking_relocation_consents_relocation_type_idx');
            $table->index(['user_id', 'status'], 'booking_relocation_consents_user_status_idx');
            $table->index('status', 'booking_relocation_consents_status_idx');
        });
    }

    private function createBookingRelocationHostResponses(): void
    {
        Schema::create('booking_relocation_host_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->foreignId('alternative_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('alternative_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->date('proposed_relocation_date')->nullable();
            $table->string('proposed_relocation_time')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('booking_relocation_id', 'booking_relocation_host_responses_relocation_idx');
            $table->index('host_user_id', 'booking_relocation_host_responses_host_idx');
            $table->index('response_type', 'booking_relocation_host_responses_type_idx');
            $table->index('alternative_sleeping_place_id', 'booking_relocation_host_responses_place_idx');
            $table->index('alternative_room_id', 'booking_relocation_host_responses_room_idx');
        });
    }

    private function createBookingRelocationGuestResponses(): void
    {
        Schema::create('booking_relocation_guest_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->foreignId('selected_option_id')->nullable()->constrained('booking_relocation_options')->nullOnDelete();
            $table->foreignId('accepted_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->date('accepted_relocation_date')->nullable();
            $table->string('accepted_relocation_time')->nullable();
            $table->timestamps();

            $table->index('booking_relocation_id', 'booking_relocation_guest_responses_relocation_idx');
            $table->index('guest_user_id', 'booking_relocation_guest_responses_guest_idx');
            $table->index('response_type', 'booking_relocation_guest_responses_type_idx');
            $table->index('selected_option_id', 'booking_relocation_guest_responses_option_idx');
            $table->index('accepted_sleeping_place_id', 'booking_relocation_guest_responses_place_idx');
        });
    }

    private function createBookingRelocationInventoryTransfers(): void
    {
        Schema::create('booking_relocation_inventory_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedBigInteger('inventory_item_id')->nullable();
            $table->string('item_name_snapshot');
            $table->string('transfer_type');
            $table->string('status')->default('pending');
            $table->foreignId('from_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('to_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('from_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('to_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('booking_relocation_id', 'booking_relocation_inventory_relocation_idx');
            $table->index('booking_id', 'booking_relocation_inventory_booking_idx');
            $table->index('inventory_item_id', 'booking_relocation_inventory_item_idx');
            $table->index('transfer_type', 'booking_relocation_inventory_type_idx');
            $table->index('status', 'booking_relocation_inventory_status_idx');
            $table->index('from_sleeping_place_id', 'booking_relocation_inventory_from_place_idx');
            $table->index('to_sleeping_place_id', 'booking_relocation_inventory_to_place_idx');
            $table->index('from_room_id', 'booking_relocation_inventory_from_room_idx');
            $table->index('to_room_id', 'booking_relocation_inventory_to_room_idx');
        });
    }

    private function createBookingRelocationStatusLogs(): void
    {
        Schema::create('booking_relocation_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->foreignId('original_booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('new_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_relocation_id', 'booking_relocation_logs_relocation_idx');
            $table->index('original_booking_id', 'booking_relocation_logs_original_idx');
            $table->index('new_booking_id', 'booking_relocation_logs_new_idx');
            $table->index('new_status', 'booking_relocation_logs_status_idx');
            $table->index('user_id', 'booking_relocation_logs_user_idx');
            $table->index('created_at', 'booking_relocation_logs_created_idx');
        });
    }

    private function createBookingRelocationEvents(): void
    {
        Schema::create('booking_relocation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_relocation_id')->constrained('booking_relocations')->cascadeOnDelete();
            $table->foreignId('original_booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('new_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_relocation_id', 'booking_relocation_events_relocation_idx');
            $table->index('original_booking_id', 'booking_relocation_events_original_idx');
            $table->index('new_booking_id', 'booking_relocation_events_new_idx');
            $table->index('event_key', 'booking_relocation_events_key_idx');
            $table->index('event_type', 'booking_relocation_events_type_idx');
            $table->index('user_id', 'booking_relocation_events_user_idx');
            $table->index(['source_type', 'source_id'], 'booking_relocation_events_source_idx');
            $table->index('occurred_at', 'booking_relocation_events_occurred_idx');
        });
    }
};
