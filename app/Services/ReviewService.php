<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;

class ReviewService
{
    /**
     * @param  array<string, mixed>  $ratings
     */
    public function createGuestReview(Booking $booking, User $guest, array $ratings, ?string $positive = null, ?string $negative = null, ?string $advice = null): Review
    {
        return Review::create([
            'booking_id' => $booking->id,
            'reviewer_id' => $guest->id,
            'reviewee_id' => $booking->host_id,
            'type' => ReviewType::GuestToPlace->value,
            'bed_id' => $booking->bed_id,
            'room_id' => $booking->room_id,
            'property_id' => $booking->property_id,
            'overall_rating' => $ratings['overall'],
            'cleanliness_rating' => $ratings['cleanliness'] ?? null,
            'safety_rating' => $ratings['safety'] ?? null,
            'location_rating' => $ratings['location'] ?? null,
            'accuracy_rating' => $ratings['accuracy'] ?? null,
            'bed_comfort_rating' => $ratings['bed_comfort'] ?? null,
            'amenities_rating' => $ratings['amenities'] ?? null,
            'communication_rating' => $ratings['communication'] ?? null,
            'neighbors_rating' => $ratings['neighbors'] ?? null,
            'value_rating' => $ratings['value'] ?? null,
            'positive_comment' => $positive,
            'negative_comment' => $negative,
            'advice' => $advice,
            'would_recommend' => $ratings['would_recommend'] ?? true,
            'would_return' => $ratings['would_return'] ?? null,
            'status' => ReviewStatus::Published->value,
        ]);
    }

    /**
     * @param  array<string, mixed>  $ratings
     */
    public function createHostReview(Booking $booking, User $host, array $ratings, ?string $comment = null): Review
    {
        return Review::create([
            'booking_id' => $booking->id,
            'reviewer_id' => $host->id,
            'reviewee_id' => $booking->guest_id,
            'type' => ReviewType::HostToGuest->value,
            'overall_rating' => $ratings['overall'],
            'rule_compliance_rating' => $ratings['rule_compliance'] ?? null,
            'tidiness_rating' => $ratings['tidiness'] ?? null,
            'communication_rating' => $ratings['communication'] ?? null,
            'punctuality_rating' => $ratings['punctuality'] ?? null,
            'positive_comment' => $comment,
            'would_recommend' => $ratings['would_recommend'] ?? true,
            'status' => ReviewStatus::Published->value,
        ]);
    }

    public function recalculateUserRating(User $user): void
    {
        $guestRating = $user->reviewsReceived()
            ->where('type', ReviewType::HostToGuest)
            ->published()
            ->avg('overall_rating');

        $hostRating = $user->reviewsReceived()
            ->where('type', ReviewType::GuestToPlace)
            ->published()
            ->avg('overall_rating');

        $user->update([
            'rating_as_guest' => $guestRating ? round($guestRating, 2) : null,
            'rating_as_host' => $hostRating ? round($hostRating, 2) : null,
        ]);
    }
}
