<?php

namespace App\Services\HostHints;

use App\Models\Property;
use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;

class HostAccessHintService
{
    use BuildsHostHints;

    /**
     * @return list<array<string, mixed>>
     */
    public function forSleepingPlace(SleepingPlace $place): array
    {
        $property = $place->property;

        if (! $property instanceof Property) {
            return [];
        }

        return collect([
            $this->missingCheckInTime($property) ? $this->hint('missing_check_in_time', 'access', 'required', 'high', 145, 'edit_access', true, true, true, true) : null,
            $this->missingCheckOutTime($property) ? $this->hint('missing_check_out_time', 'access', 'required', 'high', 140, 'edit_access', true, true, true, true) : null,
            $this->missingKeyPickupMethod($property) ? $this->hint('missing_key_pickup_method', 'access', 'required', 'high', 130, 'edit_access', true, true, true, true) : null,
            $property->accessDetails?->self_check_in_available === null ? $this->hint('missing_self_check_in_info', 'access', 'suggestion', 'medium', 75, 'edit_access') : null,
            $property->accessDetails?->can_return_at_night === null ? $this->hint('missing_night_entry_info', 'access', 'suggestion', 'medium', 70, 'edit_access') : null,
            $property->accessDetails?->has_intercom === null ? $this->hint('missing_intercom_info', 'access', 'suggestion', 'low', 40, 'edit_access') : null,
        ])->filter()->values()->all();
    }

    public function missingCheckInTime(Property $property): bool
    {
        return blank($property->host?->hostProfile?->default_check_in_time);
    }

    public function missingCheckOutTime(Property $property): bool
    {
        return blank($property->host?->hostProfile?->default_check_out_time);
    }

    public function missingKeyPickupMethod(Property $property): bool
    {
        return blank($property->accessDetails?->key_pickup_method) && blank($property->access_instructions);
    }
}
