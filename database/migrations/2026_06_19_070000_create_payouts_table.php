<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('stay_amount', 10, 2);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('compensation', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->string('currency')->default('EUR');
            $table->string('payout_method')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed, on_hold
            $table->date('scheduled_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('delay_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
