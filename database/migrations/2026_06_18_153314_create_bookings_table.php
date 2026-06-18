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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // human-readable booking number
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->unsignedTinyInteger('guests_count')->default(1);
            $table->unsignedSmallInteger('nights'); // calculated and stored
            $table->decimal('price_per_night', 8, 2);
            $table->decimal('subtotal', 8, 2);       // nights × price
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->decimal('cleaning_fee', 8, 2)->default(0);
            $table->decimal('deposit', 8, 2)->default(0);
            $table->decimal('service_fee', 8, 2)->default(0);
            $table->decimal('total', 8, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('pending_payment');
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, refunded, partially_refunded
            $table->string('cancellation_policy');
            $table->decimal('refund_amount', 8, 2)->nullable();
            $table->string('cancel_reason')->nullable();
            $table->string('cancelled_by')->nullable(); // guest, host, system
            $table->text('guest_message')->nullable();
            $table->text('host_reply')->nullable();
            $table->text('check_in_instructions')->nullable();
            $table->timestamp('guest_checked_in_at')->nullable();
            $table->timestamp('guest_checked_out_at')->nullable();
            $table->timestamp('host_confirmed_checkin_at')->nullable();
            $table->timestamp('host_confirmed_checkout_at')->nullable();
            $table->timestamp('free_cancel_before')->nullable();
            $table->timestamp('deposit_released_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['bed_id', 'check_in', 'check_out']);
            $table->index(['guest_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
