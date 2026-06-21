<?php

namespace App\Services\Stays;

use App\Models\Booking;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Collection;

class GuestRoommatesPreviewService
{
    public function __construct(
        private readonly BookingStayOccupantService $occupants,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getRoommatesForListing(Room $room): Collection
    {
        return $this->occupants->getPublicRoommateSummary($room);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getRoommatesForBooking(User $guest, Booking $booking): Collection
    {
        $booking->loadMissing('room:id');

        return $this->occupants->getPublicRoommateSummary($booking->room, $guest);
    }

    /**
     * @return array<string, mixed>
     */
    public function getWhoLivesNearbySummary(SleepingPlace $place): array
    {
        $place->loadMissing('room:id');
        $roommates = $this->getRoommatesForListing($place->room);

        return [
            'count' => $roommates->count(),
            'roommates' => $roommates->values()->all(),
            'privacy_message' => __('occupants.messages.roommates_summary_private'),
        ];
    }
}
