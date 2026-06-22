<?php

namespace App\Services\Reviews;

use App\Models\HostReputationSnapshot;
use App\Models\User;

class HostReputationService
{
    public function __construct(private readonly RatingSnapshotService $snapshots) {}

    public function getOrCreate(User $host): HostReputationSnapshot
    {
        return HostReputationSnapshot::query()->firstOrCreate(
            ['host_user_id' => $host->id],
            ['last_recalculated_at' => now()],
        );
    }

    public function refresh(User $host): HostReputationSnapshot
    {
        return $this->snapshots->recalculateHost($host);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPublicSummary(User $host): array
    {
        $snapshot = $this->getOrCreate($host);

        return [
            'overall_rating' => (float) $snapshot->overall_rating,
            'reviews_count' => $snapshot->reviews_count,
            'verified_host' => $snapshot->verified_host,
        ];
    }
}
