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
        Schema::create('booking_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_quote_id')->nullable()->constrained('booking_quotes')->nullOnDelete();
            $table->foreignId('booking_request_id')->nullable()->constrained('booking_requests')->nullOnDelete();
            $table->foreignId('booking_extension_id')->nullable()->constrained('booking_extensions')->nullOnDelete();
            $table->unsignedBigInteger('booking_relocation_id')->nullable();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->string('payment_type');
            $table->string('payment_purpose');
            $table->string('payment_method')->default('internal_test');
            $table->string('status')->default('waiting_payment');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->decimal('required_now_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->timestamp('remaining_due_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_status')->nullable();
            $table->json('provider_payload_json')->nullable();
            $table->timestamp('payment_deadline_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status'], 'booking_payments_booking_status_idx');
            $table->index('booking_quote_id', 'booking_payments_quote_idx');
            $table->index('booking_request_id', 'booking_payments_request_idx');
            $table->index('booking_extension_id', 'booking_payments_extension_idx');
            $table->index('booking_relocation_id', 'booking_payments_relocation_idx');
            $table->index(['guest_user_id', 'status'], 'booking_payments_guest_status_idx');
            $table->index(['host_user_id', 'status'], 'booking_payments_host_status_idx');
            $table->index('property_id', 'booking_payments_property_idx');
            $table->index('room_id', 'booking_payments_room_idx');
            $table->index(['sleeping_place_id', 'status'], 'booking_payments_place_status_idx');
            $table->index('payment_type', 'booking_payments_type_idx');
            $table->index('payment_purpose', 'booking_payments_purpose_idx');
            $table->index('payment_method', 'booking_payments_method_idx');
            $table->index('status', 'booking_payments_status_idx');
            $table->index('payment_deadline_at', 'booking_payments_deadline_idx');
            $table->index('paid_at', 'booking_payments_paid_at_idx');
        });

        Schema::create('booking_payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_payment_id')->constrained('booking_payments')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('status')->default('created');
            $table->string('payment_method');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('provider')->nullable();
            $table->string('provider_attempt_id')->nullable();
            $table->text('provider_redirect_url')->nullable();
            $table->string('provider_status')->nullable();
            $table->string('provider_error_code')->nullable();
            $table->text('provider_error_message')->nullable();
            $table->json('provider_payload_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index('booking_payment_id', 'booking_payment_attempts_payment_idx');
            $table->index('booking_id', 'booking_payment_attempts_booking_idx');
            $table->index('guest_user_id', 'booking_payment_attempts_guest_idx');
            $table->index('status', 'booking_payment_attempts_status_idx');
            $table->index('provider_attempt_id', 'booking_payment_attempts_provider_idx');
            $table->index('started_at', 'booking_payment_attempts_started_idx');
            $table->index('succeeded_at', 'booking_payment_attempts_succeeded_idx');
            $table->index('failed_at', 'booking_payment_attempts_failed_idx');
        });

        Schema::create('booking_payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_payment_id')->constrained('booking_payments')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('allocation_type');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->boolean('refundable')->default(false);
            $table->string('line_reference_type')->nullable();
            $table->unsignedBigInteger('line_reference_id')->nullable();
            $table->timestamps();

            $table->index('booking_payment_id', 'booking_payment_allocations_payment_idx');
            $table->index('booking_id', 'booking_payment_allocations_booking_idx');
            $table->index('allocation_type', 'booking_payment_allocations_type_idx');
            $table->index('refundable', 'booking_payment_allocations_refundable_idx');
            $table->index(['line_reference_type', 'line_reference_id'], 'booking_payment_allocations_line_idx');
        });

        Schema::create('booking_payment_deadlines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_payment_id')->nullable()->constrained('booking_payments')->cascadeOnDelete();
            $table->string('deadline_type');
            $table->timestamp('due_at');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['booking_id', 'deadline_type'], 'booking_payment_deadlines_booking_type_idx');
            $table->index('booking_payment_id', 'booking_payment_deadlines_payment_idx');
            $table->index('due_at', 'booking_payment_deadlines_due_at_idx');
            $table->index('status', 'booking_payment_deadlines_status_idx');
        });

        Schema::create('booking_refunds', function (Blueprint $table): void {
            $table->id();
            $table->string('refund_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_payment_id')->nullable()->constrained('booking_payments')->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained('sleeping_places')->cascadeOnDelete();
            $table->string('refund_type');
            $table->string('status')->default('pending');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('reason_key')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_refund_id')->nullable();
            $table->string('provider_status')->nullable();
            $table->json('provider_payload_json')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status'], 'booking_refunds_booking_status_idx');
            $table->index('booking_payment_id', 'booking_refunds_payment_idx');
            $table->index(['guest_user_id', 'status'], 'booking_refunds_guest_status_idx');
            $table->index(['host_user_id', 'status'], 'booking_refunds_host_status_idx');
            $table->index('property_id', 'booking_refunds_property_idx');
            $table->index('room_id', 'booking_refunds_room_idx');
            $table->index(['sleeping_place_id', 'status'], 'booking_refunds_place_status_idx');
            $table->index('refund_type', 'booking_refunds_type_idx');
            $table->index('status', 'booking_refunds_status_idx');
            $table->index(['source_type', 'source_id'], 'booking_refunds_source_idx');
            $table->index('created_at', 'booking_refunds_created_idx');
        });

        Schema::create('booking_payment_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_payment_id')->nullable()->constrained('booking_payments')->cascadeOnDelete();
            $table->foreignId('booking_payment_attempt_id')->nullable()->constrained('booking_payment_attempts')->cascadeOnDelete();
            $table->foreignId('booking_refund_id')->nullable()->constrained('booking_refunds')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('event_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_payment_id', 'booking_payment_status_logs_payment_idx');
            $table->index('booking_payment_attempt_id', 'booking_payment_status_logs_attempt_idx');
            $table->index('booking_refund_id', 'booking_payment_status_logs_refund_idx');
            $table->index('booking_id', 'booking_payment_status_logs_booking_idx');
            $table->index('user_id', 'booking_payment_status_logs_user_idx');
            $table->index('new_status', 'booking_payment_status_logs_status_idx');
            $table->index('event_key', 'booking_payment_status_logs_event_idx');
            $table->index('created_at', 'booking_payment_status_logs_created_idx');
        });

        Schema::create('payment_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_payment_id')->constrained('booking_payments')->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->string('status')->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->json('receipt_data_json')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index('booking_payment_id', 'payment_receipts_payment_idx');
            $table->index('booking_id', 'payment_receipts_booking_idx');
            $table->index('guest_user_id', 'payment_receipts_guest_idx');
            $table->index('status', 'payment_receipts_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('booking_payment_status_logs');
        Schema::dropIfExists('booking_refunds');
        Schema::dropIfExists('booking_payment_deadlines');
        Schema::dropIfExists('booking_payment_allocations');
        Schema::dropIfExists('booking_payment_attempts');
        Schema::dropIfExists('booking_payments');
    }
};
