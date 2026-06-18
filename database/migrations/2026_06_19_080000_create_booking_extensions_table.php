<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->date('original_check_out');
            $table->date('new_check_out');
            $table->integer('extra_nights');
            $table->decimal('extra_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_extra', 10, 2);
            $table->boolean('requires_host_approval')->default(true);
            $table->string('status')->default('pending'); // pending, approved, rejected, paid
            $table->text('host_reply')->nullable();
            $table->string('reject_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_extensions');
    }
};
