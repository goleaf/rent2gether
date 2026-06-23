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
        Schema::table('rating_events', function (Blueprint $table): void {
            $table->index('booking_stay_id', 'rating_events_booking_stay_idx');
        });

        Schema::table('rating_aggregates', function (Blueprint $table): void {
            $table->index('last_review_id', 'rating_aggregates_last_review_idx');
            $table->index('last_rating_event_id', 'rating_aggregates_last_event_idx');
        });

        Schema::table('review_events', function (Blueprint $table): void {
            $table->index('user_id', 'review_events_user_idx');
        });

        Schema::table('review_requests', function (Blueprint $table): void {
            $table->index('review_subject_user_id', 'review_requests_subject_user_idx');
            $table->index('sleeping_place_id', 'review_requests_place_idx');
            $table->index('room_id', 'review_requests_room_idx');
            $table->index('property_id', 'review_requests_property_idx');
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->index('booking_check_out_id', 'reviews_checkout_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropIndex('reviews_checkout_idx');
        });

        Schema::table('review_requests', function (Blueprint $table): void {
            $table->dropIndex('review_requests_property_idx');
            $table->dropIndex('review_requests_room_idx');
            $table->dropIndex('review_requests_place_idx');
            $table->dropIndex('review_requests_subject_user_idx');
        });

        Schema::table('review_events', function (Blueprint $table): void {
            $table->dropIndex('review_events_user_idx');
        });

        Schema::table('rating_aggregates', function (Blueprint $table): void {
            $table->dropIndex('rating_aggregates_last_event_idx');
            $table->dropIndex('rating_aggregates_last_review_idx');
        });

        Schema::table('rating_events', function (Blueprint $table): void {
            $table->dropIndex('rating_events_booking_stay_idx');
        });
    }
};
