<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // guest_to_place, host_to_guest
            $table->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();

            // Ratings (1-5)
            $table->tinyInteger('overall_rating');
            $table->tinyInteger('cleanliness_rating')->nullable();
            $table->tinyInteger('safety_rating')->nullable();
            $table->tinyInteger('location_rating')->nullable();
            $table->tinyInteger('accuracy_rating')->nullable();
            $table->tinyInteger('bed_comfort_rating')->nullable();
            $table->tinyInteger('amenities_rating')->nullable();
            $table->tinyInteger('communication_rating')->nullable();
            $table->tinyInteger('neighbors_rating')->nullable();
            $table->tinyInteger('value_rating')->nullable();

            // Host-to-guest specific
            $table->tinyInteger('rule_compliance_rating')->nullable();
            $table->tinyInteger('tidiness_rating')->nullable();
            $table->tinyInteger('punctuality_rating')->nullable();

            $table->text('positive_comment')->nullable();
            $table->text('negative_comment')->nullable();
            $table->text('advice')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->boolean('would_return')->nullable();

            $table->string('status')->default('published'); // draft, published, hidden, flagged
            $table->timestamps();

            $table->unique(['booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
