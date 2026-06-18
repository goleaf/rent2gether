<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->date('desired_check_in')->nullable();
            $table->date('desired_check_out')->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->boolean('ready_to_book')->default(false);
            $table->boolean('auto_request')->default(false);
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->string('status')->default('waiting'); // waiting, notified, booked, expired, cancelled
            $table->timestamps();

            $table->unique(['user_id', 'bed_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
    }
};
