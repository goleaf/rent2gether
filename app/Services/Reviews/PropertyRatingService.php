<?php

namespace App\Services\Reviews;

use App\Models\Property;
use App\Models\PropertyRatingSnapshot;

class PropertyRatingService
{
    public function __construct(private readonly RatingSnapshotService $snapshots) {}

    public function getOrCreate(Property $property): PropertyRatingSnapshot
    {
        return PropertyRatingSnapshot::query()->firstOrCreate(
            ['property_id' => $property->id],
            [
                'host_user_id' => $property->host_user_id ?: $property->user_id,
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function refresh(Property $property): PropertyRatingSnapshot
    {
        return $this->snapshots->recalculateProperty($property);
    }
}
