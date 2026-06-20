<?php

namespace App\Services\Occupants;

use App\Models\User;

class RoommateRatingService
{
    public function updateRoommateRating(User $user): void
    {
        $profile = $user->coLivingProfile;

        if (! $profile) {
            return;
        }

        $profile->forceFill([
            'roommate_rating_average' => $this->calculateFromReviews($user),
        ])->save();
    }

    public function calculateFromReviews(User $user): float
    {
        if ($user->coLivingProfile?->roommate_reviews_count > 0 && $user->coLivingProfile->roommate_rating_average !== null) {
            return (float) $user->coLivingProfile->roommate_rating_average;
        }

        return $user->rating_as_guest === null ? 0.0 : (float) $user->rating_as_guest;
    }

    public function getPublicRoommateRating(User $user): ?float
    {
        $profile = $user->coLivingProfile;

        if (! $profile || $profile->roommate_rating_average === null) {
            return null;
        }

        return (float) $profile->roommate_rating_average;
    }
}
