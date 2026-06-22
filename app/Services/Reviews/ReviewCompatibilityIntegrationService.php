<?php

namespace App\Services\Reviews;

use App\Models\Room;

class ReviewCompatibilityIntegrationService
{
    public function __construct(private readonly RoommateExperienceReviewService $roommates) {}

    /**
     * @return array<string, mixed>
     */
    public function buildRoommateCompatibilitySummary(Room $room): array
    {
        return $this->roommates->buildPublicRoommateSummary($room);
    }
}
