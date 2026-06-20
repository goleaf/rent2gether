<?php

namespace App\Services\Occupants;

use App\Data\Occupants\DateRange;
use App\Data\Occupants\RoommateCompatibilityData;
use App\Data\Occupants\RoomOccupantWarningData;
use App\Models\Room;
use App\Models\RoomOccupantSnapshot;
use App\Models\User;
use Illuminate\Support\Collection;

class RoommateCompatibilityService
{
    public function __construct(private readonly RoomOccupantSummaryService $summaryService) {}

    public function compareGuestWithOccupants(User $guest, Room $room, DateRange $range): RoommateCompatibilityData
    {
        $occupants = $this->summaryService->getPrivacySafeOccupants($room, $range);
        $warnings = array_values(array_filter([
            $this->detectQuietConflict($guest, $occupants),
            $this->detectSmokingConflict($guest, $occupants),
            $this->detectSleepScheduleConflict($guest, $occupants),
            $this->detectHomePresenceConflict($guest, $occupants),
        ]));

        return new RoommateCompatibilityData(
            score: max(0, 100 - (count($warnings) * 15)),
            warnings: $warnings,
            messages: $this->buildCompatibilityMessages($guest, $occupants),
        );
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $occupants
     */
    public function detectQuietConflict(User $guest, Collection $occupants): ?RoomOccupantWarningData
    {
        if ($guest->prefers_quiet === false && $occupants->contains(fn (RoomOccupantSnapshot $snapshot): bool => $snapshot->prefers_quiet_snapshot === true)) {
            return new RoomOccupantWarningData('quiet_conflict', __('occupants.warnings.quiet_conflict'));
        }

        return null;
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $occupants
     */
    public function detectSmokingConflict(User $guest, Collection $occupants): ?RoomOccupantWarningData
    {
        $guestSmokes = (bool) $guest->is_smoker;

        if (! $guestSmokes && $occupants->contains(fn (RoomOccupantSnapshot $snapshot): bool => $snapshot->smokes_snapshot === true)) {
            return new RoomOccupantWarningData('smoking_conflict', __('occupants.warnings.smoking_conflict'));
        }

        if ($guestSmokes && $occupants->contains(fn (RoomOccupantSnapshot $snapshot): bool => $snapshot->smokes_snapshot === false)) {
            return new RoomOccupantWarningData('smoking_conflict', __('occupants.warnings.smoking_conflict'));
        }

        return null;
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $occupants
     */
    public function detectSleepScheduleConflict(User $guest, Collection $occupants): ?RoomOccupantWarningData
    {
        $guestSchedule = (string) $guest->sleep_schedule;

        if ($guestSchedule === '') {
            return null;
        }

        $conflict = $occupants->contains(function (RoomOccupantSnapshot $snapshot) use ($guestSchedule): bool {
            return ($guestSchedule === 'night_owl' && $snapshot->sleep_schedule_snapshot === 'early_bird')
                || ($guestSchedule === 'early_bird' && $snapshot->sleep_schedule_snapshot === 'night_owl');
        });

        return $conflict
            ? new RoomOccupantWarningData('sleep_schedule_conflict', __('occupants.warnings.sleep_schedule_conflict'))
            : null;
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $occupants
     */
    public function detectHomePresenceConflict(User $guest, Collection $occupants): ?RoomOccupantWarningData
    {
        if ($occupants->where('home_presence_level_snapshot', 'often_home')->count() >= 3) {
            return new RoomOccupantWarningData('home_presence_conflict', __('occupants.warnings.home_presence_conflict'));
        }

        return null;
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $occupants
     * @return list<string>
     */
    public function buildCompatibilityMessages(User $guest, Collection $occupants): array
    {
        if ($occupants->isEmpty()) {
            return [__('occupants.no_occupants')];
        }

        return [
            trans_choice('occupants.occupants_count', $occupants->count(), ['count' => $occupants->count()]),
        ];
    }
}
