<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\BookingStayOccupant;
use App\Models\Room;
use App\Models\User;

class StayPrivacyService
{
    public function __construct(
        private readonly StayVisibilityService $visibility,
        private readonly StayNoteService $notes,
    ) {}

    public function canGuestViewStay(User $guest, BookingStay $stay): bool
    {
        return (int) $stay->guest_user_id === (int) $guest->id;
    }

    public function canHostViewStay(User $host, BookingStay $stay): bool
    {
        return (int) $stay->host_user_id === (int) $host->id;
    }

    public function canGuestViewRoommateSummary(User $guest, Room $room): bool
    {
        return BookingStay::query()
            ->where('room_id', $room->id)
            ->where('guest_user_id', $guest->id)
            ->active()
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function filterStayForGuest(User $guest, BookingStay $stay): array
    {
        abort_unless($this->canGuestViewStay($guest, $stay), 403);
        $stay->loadMissing(['property:id,title,city', 'room:id,property_id,user_id,title', 'sleepingPlace:id,display_name,place_number', 'occupants', 'visibilityPreference']);

        return [
            'id' => $stay->id,
            'stay_number' => $stay->stay_number,
            'status' => $stay->status,
            'property' => $stay->property?->only(['id', 'title', 'city']),
            'room' => $stay->room?->only(['id', 'title']),
            'sleeping_place' => $stay->sleepingPlace?->only(['id', 'display_name', 'place_number']),
            'check_in_date' => $stay->check_in_date?->toDateString(),
            'planned_check_out_date' => $stay->planned_check_out_date?->toDateString(),
            'nights_remaining' => $stay->nights_remaining,
            'payment_status' => $stay->payment_status,
            'deposit_status' => $stay->deposit_status,
            'notes' => $this->notes->getVisibleNotes($guest, $stay)->map->only(['note_type', 'visibility', 'note'])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterStayForHost(User $host, BookingStay $stay): array
    {
        abort_unless($this->canHostViewStay($host, $stay), 403);
        $stay->loadMissing(['guest:id,name,avatar,rating_as_guest,preferred_locale', 'property:id,title', 'room:id,property_id,user_id,title', 'sleepingPlace:id,display_name,place_number', 'occupants']);

        return [
            'id' => $stay->id,
            'stay_number' => $stay->stay_number,
            'status' => $stay->status,
            'guest' => $stay->guest?->only(['id', 'name', 'avatar', 'rating_as_guest', 'preferred_locale']),
            'property' => $stay->property?->only(['id', 'title']),
            'room' => $stay->room?->only(['id', 'title']),
            'sleeping_place' => $stay->sleepingPlace?->only(['id', 'display_name', 'place_number']),
            'check_in_date' => $stay->check_in_date?->toDateString(),
            'planned_check_out_date' => $stay->planned_check_out_date?->toDateString(),
            'nights_remaining' => $stay->nights_remaining,
            'payment_status' => $stay->payment_status,
            'deposit_status' => $stay->deposit_status,
            'has_open_complaint' => $stay->has_open_complaint,
            'has_open_maintenance' => $stay->has_open_maintenance,
            'has_payment_problem' => $stay->has_payment_problem,
            'has_deposit_issue' => $stay->has_deposit_issue,
            'occupants' => $stay->occupants->map(fn (BookingStayOccupant $occupant): array => $this->visibility->filterOccupantForHost($occupant))->values()->all(),
            'notes' => $this->notes->getVisibleNotes($host, $stay)->map->only(['note_type', 'visibility', 'note'])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterRoommateSummaryForGuest(User $guest, BookingStayOccupant $occupant): array
    {
        return $this->visibility->filterOccupantForRoommates($occupant);
    }
}
