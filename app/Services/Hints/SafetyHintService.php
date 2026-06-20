<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Services\Hints\Concerns\BuildsGuestHints;

class SafetyHintService
{
    use BuildsGuestHints;

    public function addressAfterBooking(Property $property): ?GuestHintData
    {
        if ($property->show_exact_address_before_booking) {
            return null;
        }

        return $this->hint('address_after_booking', 'address', 'privacy', 'high', 83, card: false, beforeBooking: true, dismissible: false, source: 'privacy');
    }

    public function hasPersonalLocker(SleepingPlace $place): ?GuestHintData
    {
        if (! $place->has_locker) {
            return null;
        }

        return $this->hint('personal_locker', 'safety', 'positive', 'low', 34, source: 'sleeping_place');
    }

    public function hasEmergencyContact(Property $property): ?GuestHintData
    {
        if (! filled($property->emergency_contact_name) && ! filled($property->emergency_contact_phone)) {
            return null;
        }

        return $this->hint('emergency_contact', 'safety', 'info', 'low', 28, source: 'safety');
    }
}
