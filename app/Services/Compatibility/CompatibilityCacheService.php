<?php

namespace App\Services\Compatibility;

use App\Data\Compatibility\CompatibilityResultData;
use App\Data\Occupants\DateRange;
use App\Models\CompatibilityResult;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;

class CompatibilityCacheService
{
    public function getCached(User $user, SleepingPlace $place, DateRange $range): ?CompatibilityResult
    {
        return CompatibilityResult::query()
            ->fresh()
            ->where('user_id', $user->id)
            ->where('sleeping_place_id', $place->id)
            ->whereDate('check_in_date', $range->checkIn->toDateString())
            ->whereDate('check_out_date', $range->checkOut->toDateString())
            ->latest('calculated_at')
            ->first();
    }

    public function store(User $user, SleepingPlace $place, DateRange $range, CompatibilityResultData $result): CompatibilityResult
    {
        $place->loadMissing('room', 'property');

        return CompatibilityResult::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'sleeping_place_id' => $place->id,
                'check_in_date' => $range->checkIn->toDateString(),
                'check_out_date' => $range->checkOut->toDateString(),
            ],
            [
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'nights_count' => $range->nightsCount,
                'compatibility_score' => $result->score,
                'fit_status' => $result->fitStatus,
                'positive_reasons_json' => array_map(fn ($reason): array => $reason->toArray(), $result->positiveReasons),
                'warning_reasons_json' => array_map(fn ($reason): array => $reason->toArray(), $result->warningReasons),
                'blocking_reasons_json' => array_map(fn ($reason): array => $reason->toArray(), $result->blockingReasons),
                'calculated_at' => now(),
                'expires_at' => now()->addMinutes(30),
            ],
        );
    }

    public function forgetForUser(User $user): void
    {
        CompatibilityResult::query()
            ->where('user_id', $user->id)
            ->delete();
    }

    public function forgetForRoom(Room $room): void
    {
        CompatibilityResult::query()
            ->where('room_id', $room->id)
            ->delete();
    }

    public function forgetForSleepingPlace(SleepingPlace $place): void
    {
        CompatibilityResult::query()
            ->where('sleeping_place_id', $place->id)
            ->delete();
    }
}
