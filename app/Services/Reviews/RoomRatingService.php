<?php

namespace App\Services\Reviews;

use App\Models\Room;
use App\Models\RoomRatingSnapshot;

class RoomRatingService
{
    public function __construct(private readonly RatingSnapshotService $snapshots) {}

    public function getOrCreate(Room $room): RoomRatingSnapshot
    {
        return RoomRatingSnapshot::query()->firstOrCreate(
            ['room_id' => $room->id],
            [
                'property_id' => $room->property_id,
                'host_user_id' => $room->property?->host_user_id ?: $room->property?->user_id,
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function refresh(Room $room): RoomRatingSnapshot
    {
        return $this->snapshots->recalculateRoom($room);
    }
}
