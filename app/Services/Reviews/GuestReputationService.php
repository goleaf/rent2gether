<?php

namespace App\Services\Reviews;

use App\Models\Booking;
use App\Models\GuestReputationSnapshot;
use App\Models\User;

class GuestReputationService
{
    public function __construct(private readonly RatingSnapshotService $snapshots) {}

    public function getOrCreate(User $guest): GuestReputationSnapshot
    {
        return GuestReputationSnapshot::query()->firstOrCreate(
            ['guest_user_id' => $guest->id],
            ['last_recalculated_at' => now()],
        );
    }

    public function refresh(User $guest): GuestReputationSnapshot
    {
        return $this->snapshots->recalculateGuest($guest);
    }

    /**
     * @return array<string, mixed>
     */
    public function getHostVisibleSummary(User $host, User $guest, ?Booking $booking = null): array
    {
        if (! app(ReviewPrivacyService::class)->canViewGuestReputation($host, $guest, $booking)) {
            return [];
        }

        $snapshot = $this->getOrCreate($guest);

        return [
            'overall_rating' => (float) $snapshot->overall_rating,
            'reviews_count' => $snapshot->reviews_count,
            'completed_stays_count' => $snapshot->completed_stays_count,
            'recommended_by_hosts_count' => $snapshot->recommended_by_hosts_count,
        ];
    }
}
