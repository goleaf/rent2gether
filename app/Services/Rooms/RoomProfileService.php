<?php

namespace App\Services\Rooms;

use App\Models\Room;
use App\Models\User;

class RoomProfileService
{
    public function __construct(
        private readonly RoomGuestSummaryService $guestSummary,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildForGuest(Room $room, mixed $context = null): array
    {
        $viewer = $context instanceof User ? $context : auth()->user();

        return $this->guestSummary->build($room, $viewer instanceof User ? $viewer : null);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildForHost(Room $room): array
    {
        return $this->guestSummary->build($room, $room->property?->host);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateMainInfo(User $host, Room $room, array $data): void
    {
        abort_unless((int) $room->property?->host_user_id === $host->id, 403);

        $room->update($data);
    }

    public function updateCounts(Room $room): void
    {
        $room->loadCount(['sleepingPlaces as active_sleeping_places_count']);
        $active = (int) $room->active_sleeping_places_count;

        $room->forceFill([
            'sleeping_places_count' => max((int) $room->sleeping_places_count, $active),
            'active_sleeping_places_count' => $active,
        ])->save();
    }
}
