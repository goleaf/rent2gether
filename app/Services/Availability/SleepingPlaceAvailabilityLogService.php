<?php

namespace App\Services\Availability;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceAvailabilityStatusLog;
use App\Models\User;
use Carbon\CarbonInterface;

class SleepingPlaceAvailabilityLogService
{
    public function record(
        SleepingPlace $place,
        ?CarbonInterface $date,
        ?string $oldStatus,
        string $newStatus,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?User $user = null,
        ?string $note = null,
    ): SleepingPlaceAvailabilityStatusLog {
        return SleepingPlaceAvailabilityStatusLog::query()->create([
            'sleeping_place_id' => $place->id,
            'date' => $date?->toDateString(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'user_id' => $user?->id,
            'note' => $note,
        ]);
    }
}
