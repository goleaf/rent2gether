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
        Schema::create('booking_quotes', function (Blueprint $table): void {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rental_mode')->default('nightly');
            $table->date('check_in_date');
            $table->string('check_in_time')->nullable();
            $table->date('check_out_date');
            $table->string('check_out_time')->nullable();
            $table->string('check_in_window')->nullable();
            $table->string('check_out_window')->nullable();
            $table->unsignedInteger('nights_count')->default(0);
            $table->unsignedInteger('chargeable_days_count')->default(0);
            $table->unsignedInteger('calendar_presence_days_count')->default(0);
            $table->unsignedTinyInteger('guests_count')->default(1);
            $table->unsignedTinyInteger('included_guests_count')->default(1);
            $table->unsignedTinyInteger('extra_guests_count')->default(0);
            $table->boolean('early_check_in_requested')->default(false);
            $table->boolean('late_check_out_requested')->default(false);
            $table->boolean('flexible_check_in')->default(false);
            $table->boolean('flexible_check_out')->default(false);
            $table->boolean('requires_host_time_approval')->default(false);
            $table->text('check_in_comment')->nullable();
            $table->text('check_out_comment')->nullable();
            $table->string('availability_status')->default('unchecked');
            $table->string('validation_status')->default('unchecked');
            $table->string('pricing_status')->default('unchecked');
            $table->decimal('accommodation_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('cleaning_fee_amount', 10, 2)->default(0);
            $table->decimal('service_fee_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('city_fee_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('total_without_deposit', 10, 2)->default(0);
            $table->decimal('total_payable', 10, 2)->default(0);
            $table->decimal('host_payout_preview_amount', 10, 2)->default(0);
            $table->decimal('refundable_amount', 10, 2)->default(0);
            $table->decimal('non_refundable_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->timestamp('free_cancellation_until')->nullable();
            $table->timestamp('cancellation_penalty_starts_at')->nullable();
            $table->timestamp('payment_deadline_at')->nullable();
            $table->timestamp('host_payout_due_at')->nullable();
            $table->timestamp('guest_check_in_reminder_at')->nullable();
            $table->timestamp('guest_check_out_reminder_at')->nullable();
            $table->timestamp('host_check_in_reminder_at')->nullable();
            $table->timestamp('host_check_out_reminder_at')->nullable();
            $table->timestamp('deposit_review_start_at')->nullable();
            $table->timestamp('review_request_at')->nullable();
            $table->string('promo_code')->nullable();
            $table->string('promo_code_status')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['sleeping_place_id', 'check_in_date']);
            $table->index(['sleeping_place_id', 'check_out_date']);
            $table->index(['room_id', 'check_in_date']);
            $table->index(['property_id', 'check_in_date']);
            $table->index(['host_user_id', 'status']);
            $table->index('check_in_date');
            $table->index('check_out_date');
            $table->index('expires_at');
            $table->index('status');
            $table->index('availability_status');
            $table->index('validation_status');
            $table->index('pricing_status');
        });

        Schema::create('booking_quote_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_quote_id')->constrained()->cascadeOnDelete();
            $table->string('line_type');
            $table->string('label_key');
            $table->date('date')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_amount', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_fee')->default(false);
            $table->boolean('is_deposit')->default(false);
            $table->boolean('is_refundable')->default(true);
            $table->boolean('is_payable_now')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['booking_quote_id', 'line_type']);
            $table->index(['booking_quote_id', 'sort_order']);
            $table->index('date');
            $table->index('is_discount');
            $table->index('is_deposit');
            $table->index('is_refundable');
        });

        Schema::create('booking_quote_validation_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_quote_id')->constrained()->cascadeOnDelete();
            $table->string('validation_key');
            $table->string('severity')->default('error');
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->boolean('blocking')->default(true);
            $table->boolean('visible_to_guest')->default(true);
            $table->boolean('visible_to_host')->default(false);
            $table->timestamps();

            $table->index('booking_quote_id');
            $table->index('validation_key');
            $table->index('blocking');
            $table->index('severity');
            $table->index('visible_to_guest');
        });

        Schema::create('booking_timeline_dates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_quote_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->timestamp('scheduled_at');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['booking_quote_id', 'event_key']);
            $table->index(['booking_id', 'event_key']);
            $table->index('scheduled_at');
            $table->index('status');
        });

        Schema::create('booking_quote_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_quote_id')->constrained()->cascadeOnDelete();
            $table->string('suggestion_type');
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->unsignedInteger('nights_count')->nullable();
            $table->decimal('price_preview_amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('message_key');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('booking_quote_id');
            $table->index('suggestion_type');
            $table->index('sleeping_place_id');
            $table->index('room_id');
            $table->index('property_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_quote_suggestions');
        Schema::dropIfExists('booking_timeline_dates');
        Schema::dropIfExists('booking_quote_validation_results');
        Schema::dropIfExists('booking_quote_lines');
        Schema::dropIfExists('booking_quotes');
    }
};
