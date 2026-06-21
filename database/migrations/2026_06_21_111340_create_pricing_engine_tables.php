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
        Schema::create('sleeping_place_pricing_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 3)->default('EUR');
            $table->decimal('base_nightly_price', 10, 2);
            $table->decimal('weekday_price', 10, 2)->nullable();
            $table->decimal('weekend_price', 10, 2)->nullable();
            $table->decimal('holiday_price', 10, 2)->nullable();
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->string('pricing_strategy')->default('per_night_with_discounts');
            $table->json('weekend_days_json')->nullable();
            $table->boolean('extra_guest_allowed')->default(false);
            $table->unsignedTinyInteger('included_guests_count')->default(1);
            $table->unsignedTinyInteger('max_guests_count')->default(1);
            $table->decimal('extra_guest_fee', 10, 2)->nullable();
            $table->string('early_check_in_mode')->default('not_allowed');
            $table->decimal('early_check_in_fee', 10, 2)->nullable();
            $table->string('late_checkout_mode')->default('not_allowed');
            $table->decimal('late_checkout_fee', 10, 2)->nullable();
            $table->decimal('cleaning_fee', 10, 2)->nullable();
            $table->boolean('deposit_required')->default(false);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->boolean('deposit_payable_now')->default(true);
            $table->boolean('deposit_refundable')->default(true);
            $table->string('guest_service_fee_type')->nullable();
            $table->decimal('guest_service_fee_value', 10, 2)->nullable();
            $table->string('host_service_fee_type')->nullable();
            $table->decimal('host_service_fee_value', 10, 2)->nullable();
            $table->string('tax_fee_type')->nullable();
            $table->decimal('tax_fee_value', 10, 2)->nullable();
            $table->string('city_fee_type')->nullable();
            $table->decimal('city_fee_value', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('sleeping_place_id');
            $table->index('active');
            $table->index('currency');
            $table->index('base_nightly_price');
        });

        Schema::create('sleeping_place_date_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('price_type')->default('manual_override');
            $table->unsignedInteger('min_nights')->nullable();
            $table->unsignedInteger('max_nights')->nullable();
            $table->boolean('check_in_allowed')->default(true);
            $table->boolean('check_out_allowed')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'date']);
            $table->index('date');
            $table->index('price_type');
            $table->index('check_in_allowed');
            $table->index('check_out_allowed');
        });

        Schema::create('sleeping_place_discount_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('discount_type');
            $table->string('name');
            $table->string('value_type');
            $table->decimal('value', 10, 2);
            $table->unsignedInteger('min_nights')->nullable();
            $table->unsignedInteger('max_nights')->nullable();
            $table->unsignedInteger('min_days_before_check_in')->nullable();
            $table->unsignedInteger('max_days_before_check_in')->nullable();
            $table->boolean('new_guest_only')->default(false);
            $table->boolean('allow_stacking')->default(false);
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'active']);
            $table->index('discount_type');
            $table->index('min_nights');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index('priority');
        });

        Schema::create('promo_codes', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('discount_type')->default('promo_code');
            $table->string('value_type');
            $table->decimal('value', 10, 2);
            $table->string('currency', 3)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable();
            $table->decimal('min_booking_amount', 10, 2)->nullable();
            $table->unsignedInteger('min_nights')->nullable();
            $table->boolean('new_guest_only')->default(false);
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('active');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index('sleeping_place_id');
            $table->index('property_id');
            $table->index('host_user_id');
        });

        Schema::create('promo_code_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->index(['promo_code_id', 'user_id']);
            $table->index('user_id');
            $table->index('booking_quote_id');
            $table->index('booking_id');
            $table->index('redeemed_at');
        });

        Schema::create('booking_price_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_quote_id')->nullable()->constrained()->nullOnDelete();
            $table->json('pricing_settings_snapshot_json')->nullable();
            $table->json('quote_lines_snapshot_json')->nullable();
            $table->json('discounts_snapshot_json')->nullable();
            $table->json('promo_code_snapshot_json')->nullable();
            $table->decimal('accommodation_before_discount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('accommodation_after_discount', 10, 2)->default(0);
            $table->decimal('early_check_in_fee', 10, 2)->default(0);
            $table->decimal('late_checkout_fee', 10, 2)->default(0);
            $table->decimal('extra_guest_fee', 10, 2)->default(0);
            $table->decimal('cleaning_fee', 10, 2)->default(0);
            $table->decimal('guest_service_fee', 10, 2)->default(0);
            $table->decimal('host_service_fee', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('city_fee', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('total_without_deposit', 10, 2)->default(0);
            $table->decimal('total_payable', 10, 2)->default(0);
            $table->decimal('host_payout_amount', 10, 2)->default(0);
            $table->decimal('refundable_amount', 10, 2)->default(0);
            $table->decimal('non_refundable_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();

            $table->index('booking_quote_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_price_snapshots');
        Schema::dropIfExists('promo_code_redemptions');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('sleeping_place_discount_rules');
        Schema::dropIfExists('sleeping_place_date_prices');
        Schema::dropIfExists('sleeping_place_pricing_settings');
    }
};
