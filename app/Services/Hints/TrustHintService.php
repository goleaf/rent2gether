<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Models\SleepingPlace;
use App\Services\Hints\Concerns\BuildsGuestHints;

class TrustHintService
{
    use BuildsGuestHints;

    public function hasHighCleanlinessRating(SleepingPlace $place): ?GuestHintData
    {
        $place->loadMissing('property:id,cleanliness_level');
        $rating = $place->getAttribute('published_cleanliness_rating');

        if ((float) $rating < 4.7 && ! in_array($this->value($place->property?->cleanliness_level), ['high', 'excellent', 'very_good'], true)) {
            return null;
        }

        return $this->hint('high_cleanliness_rating', 'trust', 'positive', 'medium', 74, card: true, source: 'trust');
    }

    public function hasHighSafetyRating(SleepingPlace $place): ?GuestHintData
    {
        $place->loadMissing('property:id,safety_level');
        $rating = $place->getAttribute('published_safety_rating');

        if ((float) $rating < 4.7 && ! in_array($this->value($place->property?->safety_level), ['high', 'excellent', 'very_good'], true)) {
            return null;
        }

        return $this->hint('high_safety_rating', 'trust', 'positive', 'medium', 68, card: true, source: 'trust');
    }

    public function hasManyReviews(SleepingPlace $place): ?GuestHintData
    {
        $count = (int) ($place->published_reviews_count ?? $place->reviews()->visible()->count());

        if ($count < 10) {
            return null;
        }

        return $this->hint('many_reviews', 'trust', 'positive', 'low', 38, ['count' => $count], source: 'trust');
    }

    public function isNewListing(SleepingPlace $place): ?GuestHintData
    {
        if (! $place->created_at?->greaterThanOrEqualTo(now()->subDays(30))) {
            return null;
        }

        return $this->hint('new_listing', 'trust', 'info', 'low', 20, source: 'trust');
    }

    public function hasNoSeriousComplaints(SleepingPlace $place): ?GuestHintData
    {
        return null;
    }

    public function isOftenBooked(SleepingPlace $place): ?GuestHintData
    {
        $count = (int) ($place->getAttribute('guest_hint_bookings_count') ?? $place->bookings()->count());

        if ($count < 5) {
            return null;
        }

        return $this->hint('often_booked', 'trust', 'positive', 'medium', 61, card: true, source: 'trust');
    }

    public function isOftenFavorited(SleepingPlace $place): ?GuestHintData
    {
        $count = (int) ($place->getAttribute('guest_hint_favorites_count') ?? $place->favorites()->count());

        if ($count < 5) {
            return null;
        }

        return $this->hint('often_favorited', 'trust', 'positive', 'medium', 57, card: true, source: 'trust');
    }
}
