<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Data\Occupants\DateRange;
use App\Models\Room;
use App\Services\Hints\Concerns\BuildsGuestHints;
use App\Services\Occupants\RoomOccupantSummaryService;

class RoomOccupancyHintService
{
    use BuildsGuestHints;

    public function __construct(private readonly RoomOccupantSummaryService $occupants) {}

    public function getPeopleInRoomHint(Room $room, DateRange $range): ?GuestHintData
    {
        $count = $this->occupants->countOccupantsForDates($room, $range);

        if ($count <= 0) {
            $count = (int) ($room->current_guests_count ?? $room->occupied_places_count ?? 0);
        }

        if ($count <= 0) {
            return null;
        }

        return $this->hint('people_already_in_room', 'occupants', 'info', 'medium', 66, ['count' => $count], card: true, beforeBooking: true, source: 'occupants');
    }

    public function getRoomAlmostFullHint(Room $room, DateRange $range): ?GuestHintData
    {
        $count = $this->occupants->countOccupantsForDates($room, $range);
        $capacity = max(1, (int) ($room->sleeping_places_count ?: $room->max_guests ?: 1));

        if ($count < max(2, $capacity - 1)) {
            return null;
        }

        return $this->hint('room_almost_full', 'occupants', 'warning', 'medium', 69, card: true, beforeBooking: true, source: 'occupants');
    }

    public function getQuietOccupantsHint(Room $room, DateRange $range): ?GuestHintData
    {
        $summary = $this->occupants->getPreBookingSummary($room, $range);

        if (! collect($summary->badges)->contains(__('occupants.quiet'))) {
            return null;
        }

        return $this->hint('quiet_occupants', 'occupants', 'positive', 'low', 40, source: 'occupants');
    }

    public function getLongTermOccupantHint(Room $room, DateRange $range): ?GuestHintData
    {
        $summary = $this->occupants->getPreBookingSummary($room, $range);

        if (! collect($summary->badges)->contains(__('occupants.long_term_guest'))) {
            return null;
        }

        return $this->hint('long_term_occupant', 'occupants', 'info', 'low', 39, source: 'occupants');
    }
}
