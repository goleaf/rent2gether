<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // checkin_problem, dirty, mismatch, unsafe, neighbor_issue, theft, damage, etc.
            $table->text('description');
            $table->json('photos')->nullable();
            $table->string('urgency')->default('normal'); // low, normal, high, critical
            $table->string('desired_resolution')->nullable(); // refund, cancel, transfer, warning, deposit_hold
            $table->text('respondent_reply')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->decimal('compensation_amount', 10, 2)->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->decimal('deposit_withheld', 10, 2)->nullable();
            $table->string('status')->default('open'); // open, awaiting_response, investigating, resolved, closed, dismissed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
