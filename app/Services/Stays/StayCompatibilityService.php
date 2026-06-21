<?php

namespace App\Services\Stays;

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Collection;

class StayCompatibilityService
{
    /**
     * @return array{score:int,warnings:list<string>}
     */
    public function compareGuestWithCurrentRoom(User $guest, Room $room): array
    {
        $warnings = $this->buildRoommateCompatibilityWarnings($guest, $room);

        return [
            'score' => $this->calculateRoomFitScore($guest, $room),
            'warnings' => $warnings->all(),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    public function buildRoommateCompatibilityWarnings(User $guest, Room $room): Collection
    {
        $snapshot = app(RoomOccupancySnapshotService::class)->getOrCreate($room);
        $warnings = collect();

        if ($guest->prefers_quiet && ($snapshot->late_sleep_count > 0 || $snapshot->night_work_count > 0)) {
            $warnings->push('guest_needs_quiet_but_room_has_late_sleepers');
        }

        if ($guest->is_smoker && $snapshot->non_smokers_count > 0) {
            $warnings->push('guest_smokes_but_room_non_smoking');
        }

        if ((bool) $room->is_private === false && (bool) ($guest->willing_to_share_room === false)) {
            $warnings->push('guest_wants_private_room_but_room_is_shared');
        }

        return $warnings->values();
    }

    public function calculateRoomFitScore(User $guest, Room $room): int
    {
        return max(0, 100 - ($this->buildRoommateCompatibilityWarnings($guest, $room)->count() * 20));
    }
}
