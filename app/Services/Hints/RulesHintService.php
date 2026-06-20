<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\Hints\Concerns\BuildsGuestHints;

class RulesHintService
{
    use BuildsGuestHints;

    public function strictQuietHours(Room $room): ?GuestHintData
    {
        $room->loadMissing('comfortDetails:id,room_id,quiet_hours_enabled');

        if ($room->can_talk_at_night !== false && $room->comfortDetails?->quiet_hours_enabled !== true && ! $this->containsRule($room->rules, 'quiet_hours')) {
            return null;
        }

        return $this->hint('strict_quiet_hours', 'rules', 'rule', 'high', 86, card: true, beforeBooking: true, source: 'rules');
    }

    public function smokingForbidden(Property $property, Room $room): ?GuestHintData
    {
        if (! $this->containsRule($property->rules, 'no_smoking') && ! $this->containsRule($room->rules, 'no_smoking')) {
            return null;
        }

        return $this->hint('smoking_forbidden', 'rules', 'rule', 'high', 85, beforeBooking: true, dismissible: false, source: 'rules');
    }

    public function petsForbidden(Property $property): ?GuestHintData
    {
        if (! $this->containsRule($property->rules, 'no_pets')) {
            return null;
        }

        return $this->hint('pets_forbidden', 'rules', 'rule', 'high', 85, beforeBooking: true, dismissible: false, source: 'rules');
    }

    public function kitchenClosesAtNight(Property $property): ?GuestHintData
    {
        if (! $this->containsRule($property->rules, 'kitchen_closes_at_night')) {
            return null;
        }

        return $this->hint('kitchen_closes_at_night', 'rules', 'rule', 'medium', 46, beforeBooking: true, source: 'rules');
    }

    public function guestsForbidden(Property $property): ?GuestHintData
    {
        if (! $this->containsRule($property->rules, 'no_guests')) {
            return null;
        }

        return $this->hint('guests_forbidden', 'rules', 'rule', 'medium', 44, source: 'rules');
    }

    public function identityVerificationRequired(SleepingPlace $place): ?GuestHintData
    {
        $place->loadMissing('rules:id,slug');

        if (! $place->rules->contains(fn ($rule): bool => in_array($rule->slug, ['identity_verification_required', 'verified_guest_only'], true))) {
            return null;
        }

        return $this->hint('identity_verification_required', 'rules', 'warning', 'high', 88, beforeBooking: true, dismissible: false, source: 'rules');
    }

    private function containsRule(mixed $rules, string $rule): bool
    {
        return collect(is_array($rules) ? $rules : [])
            ->map(fn (mixed $item): string => (string) $item)
            ->contains($rule);
    }
}
