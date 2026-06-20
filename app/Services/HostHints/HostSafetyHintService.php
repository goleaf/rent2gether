<?php

namespace App\Services\HostHints;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;

class HostSafetyHintService
{
    use BuildsHostHints;

    /**
     * @return list<array<string, mixed>>
     */
    public function forSleepingPlace(SleepingPlace $place): array
    {
        $property = $place->property;
        $room = $place->room;

        return collect([
            $property instanceof Property && $this->missingEmergencyContact($property) ? $this->hint('missing_emergency_contact', 'safety', 'required', 'high', 135, 'edit_safety', true, true, true, true) : null,
            $room instanceof Room && ! $room->has_lock ? $this->hint('missing_room_lock_info', 'safety', 'suggestion', 'medium', 80, 'edit_room_access') : null,
            $room instanceof Room && (int) $room->current_guests_count === 0 ? $this->hint('add_current_occupants_count', 'room', 'suggestion', 'medium', 100, 'edit_room_occupants', true, true, true, true) : null,
            ! $place->has_locker ? $this->hint('add_locker_info', 'sleeping_place', 'suggestion', 'high', 115, 'edit_storage', true, true, true, true) : null,
            $property instanceof Property && ! $property->has_security ? $this->hint('missing_problem_instructions', 'safety', 'suggestion', 'low', 45, 'edit_safety') : null,
        ])->filter()->values()->all();
    }

    public function missingEmergencyContact(Property $property): bool
    {
        return blank($property->emergency_contact_name)
            && blank($property->emergency_contact_phone)
            && $property->accessDetails?->emergency_contact_available !== true;
    }
}
