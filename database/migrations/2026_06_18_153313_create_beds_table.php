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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // single, double, bunk_top, bunk_bottom, sofa, sofa_bed, mattress, fold_out, capsule
            $table->unsignedTinyInteger('max_guests')->default(1);
            $table->decimal('price_per_night', 8, 2);
            $table->decimal('price_weekend', 8, 2)->nullable();
            $table->decimal('price_weekly', 8, 2)->nullable();
            $table->decimal('price_monthly', 8, 2)->nullable();
            $table->decimal('cleaning_fee', 8, 2)->nullable();
            $table->decimal('deposit', 8, 2)->nullable();
            $table->unsignedTinyInteger('discount_weekly')->default(0);   // %
            $table->unsignedTinyInteger('discount_monthly')->default(0);  // %
            $table->unsignedSmallInteger('min_nights')->default(1);
            $table->unsignedSmallInteger('max_nights')->nullable();
            $table->unsignedTinyInteger('cleaning_gap_hours')->default(0); // buffer between bookings
            $table->boolean('instant_book')->default(false);
            $table->string('cancellation_policy')->default('flexible'); // flexible, moderate, strict, non_refundable
            $table->boolean('has_locker')->default(false);
            $table->boolean('has_outlet')->default(false);
            $table->boolean('has_lamp')->default(false);
            $table->boolean('has_curtain')->default(false);
            $table->boolean('has_shelf')->default(false);
            $table->boolean('has_luggage_space')->default(false);
            $table->boolean('has_linen')->default(false);
            $table->boolean('has_towel')->default(false);
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, hidden, maintenance, closed
            $table->softDeletes();
            $table->timestamps();

            $table->index(['room_id', 'status']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
