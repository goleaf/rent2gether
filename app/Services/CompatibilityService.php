<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\GuestPreference;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CompatibilityService
{
    /**
     * @return array{
     *     score:int,
     *     fit_level:string,
     *     positive_reasons:list<string>,
     *     warning_reasons:list<string>,
     *     positive_reason_keys:list<string>,
     *     warning_reason_keys:list<string>,
     *     matches:list<string>,
     *     warnings:list<string>
     * }
     */
    public function check(User $guest, Bed|SleepingPlace $sleepingPlace): array
    {
        $guest->loadMissing('guestPreference');
        $sleepingPlace->loadMissing('room.property');

        $preferences = $guest->guestPreference ?: new GuestPreference([
            'user_id' => $guest->id,
            'preferred_currency' => 'EUR',
        ]);
        $room = $sleepingPlace->room;
        $property = $room->property;

        return $this->evaluate($preferences, $property, $room, $sleepingPlace);
    }

    /**
     * @return array{
     *     score:int,
     *     fit_level:string,
     *     positive_reasons:list<string>,
     *     warning_reasons:list<string>,
     *     positive_reason_keys:list<string>,
     *     warning_reason_keys:list<string>,
     *     matches:list<string>,
     *     warnings:list<string>
     * }
     */
    public function evaluate(GuestPreference $preferences, Property $property, Room $room, Bed|SleepingPlace $sleepingPlace): array
    {
        $score = 100;
        $positive = [];
        $warnings = [];

        $addPositive = function (string $key) use (&$positive): void {
            $positive[$key] = __('compatibility.reasons.positive.'.$key);
        };

        $addWarning = function (string $key, int $penalty) use (&$warnings, &$score): void {
            $warnings[$key] = __('compatibility.reasons.warning.'.$key);
            $score -= $penalty;
        };

        $rules = $this->values($property, 'rules');
        $amenities = $this->values($property, 'amenities');
        $price = $this->price($sleepingPlace);
        $currency = $this->currency($sleepingPlace, $preferences);

        if ($preferences->preferred_city_id && $property->city_id && (int) $preferences->preferred_city_id !== (int) $property->city_id) {
            $addWarning('city_mismatch', 12);
        }

        if ($preferences->preferred_room_type && $this->value($room->type) === $preferences->preferred_room_type) {
            $addPositive('room_type');
        }

        if ($preferences->preferred_sleeping_place_type && $this->value($sleepingPlace->type) === $preferences->preferred_sleeping_place_type) {
            $addPositive('sleeping_place_type');
        }

        if ($preferences->preferred_currency && $currency !== $preferences->preferred_currency) {
            $addWarning('currency_mismatch', 8);
        }

        if ($preferences->preferred_budget_min !== null && $price < (float) $preferences->preferred_budget_min) {
            $addWarning('price_below_budget', 6);
        }

        if ($preferences->preferred_budget_max !== null && $price > (float) $preferences->preferred_budget_max) {
            $addWarning('price_above_budget', 22);
        } elseif ($preferences->preferred_budget_min !== null || $preferences->preferred_budget_max !== null) {
            $addPositive('price_in_budget');
        }

        if ($preferences->wants_wifi) {
            $this->containsAny($amenities, ['wifi', 'wi-fi'])
                ? $addPositive('wifi')
                : $addWarning('missing_wifi', 8);
        }

        if ($preferences->wants_kitchen) {
            $this->containsAny($amenities, ['kitchen'])
                ? $addPositive('kitchen')
                : $addWarning('missing_kitchen', 8);
        }

        if ($preferences->wants_washing_machine) {
            $this->containsAny($amenities, ['washer', 'washing_machine', 'laundry'])
                ? $addPositive('washing_machine')
                : $addWarning('missing_washing_machine', 6);
        }

        if ($preferences->wants_locker) {
            $this->bool($sleepingPlace, 'has_locker')
                ? $addPositive('personal_locker')
                : $addWarning('missing_locker', 10);
        }

        if ($preferences->wants_lower_bunk) {
            if ($this->isUpperBunk($sleepingPlace)) {
                $addWarning('upper_bunk_conflict', 24);
            } elseif ($this->isLowerBunk($sleepingPlace)) {
                $addPositive('lower_bunk');
            }
        }

        if ($preferences->needs_workspace) {
            $this->hasWorkspace($room, $sleepingPlace)
                ? $addPositive('workspace')
                : $addWarning('missing_workspace', 12);
        }

        if ($preferences->needs_quiet_hours || $preferences->sleep_schedule === 'early_bird') {
            $this->hasQuietFit($room, $rules)
                ? $addPositive('quiet_hours')
                : $addWarning('missing_quiet_hours', 16);
        }

        if ($preferences->avoids_smoking) {
            $this->containsAny($rules, ['no_smoking'])
                ? $addPositive('no_smoking_rule')
                : $addWarning('smoking_conflict', 28);
        }

        if ($preferences->avoids_pets) {
            $this->containsAny($rules, ['no_pets'])
                ? $addPositive('no_pets_rule')
                : $addWarning('pets_conflict', 18);
        }

        if ($this->hasPetAllergy($preferences) && ! $this->containsAny($rules, ['no_pets'])) {
            $addWarning('pets_allergy_conflict', 42);
        }

        if ($preferences->avoids_mixed_room && $this->roomGender($room) === 'mixed') {
            $addWarning('mixed_room_warning', 18);
        }

        if ($preferences->needs_accessibility) {
            $this->bool($sleepingPlace, 'is_accessible')
                ? $addPositive('accessibility')
                : $addWarning('missing_accessibility', 24);
        }

        if ($preferences->max_people_in_room !== null) {
            $roomCapacity = $this->roomCapacity($room);

            if ($roomCapacity !== null && $roomCapacity > (int) $preferences->max_people_in_room) {
                $addWarning('room_too_busy', 12);
            } elseif ($roomCapacity !== null) {
                $addPositive('room_people_ok');
            }
        }

        if ($preferences->max_walking_distance_to_transport_meters !== null && $property->distance_to_transport_meters !== null) {
            if ((int) $property->distance_to_transport_meters > (int) $preferences->max_walking_distance_to_transport_meters) {
                $addWarning('transport_too_far', 10);
            } else {
                $addPositive('transport_close');
            }
        }

        if ($preferences->baggage_size === 'large') {
            $this->bool($sleepingPlace, 'has_luggage_space')
                ? $addPositive('luggage_space')
                : $addWarning('baggage_space_limited', 10);
        }

        $score = max(0, min(100, $score));
        $fitLevel = match (true) {
            $score >= 85 => 'great',
            $score >= 70 => 'good',
            $score >= 45 => 'attention',
            default => 'not_suitable',
        };

        return [
            'score' => $score,
            'fit_level' => $fitLevel,
            'positive_reasons' => array_values($positive),
            'warning_reasons' => array_values($warnings),
            'positive_reason_keys' => array_keys($positive),
            'warning_reason_keys' => array_keys($warnings),
            'matches' => array_values($positive),
            'warnings' => array_values($warnings),
        ];
    }

    /** @return list<string> */
    private function values(Model $model, string $attribute): array
    {
        $value = $model->getAttribute($attribute);

        if ($value instanceof Collection) {
            return $value->pluck('name_normalized')->filter()->values()->all();
        }

        if (is_array($value)) {
            return collect($value)->map(fn (mixed $item): string => str((string) $item)->lower()->replace(' ', '_')->toString())->all();
        }

        return [];
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return is_string($value) ? $value : null;
    }

    private function bool(Model $model, string $attribute): bool
    {
        return (bool) $model->getAttribute($attribute);
    }

    /** @param list<string> $values */
    private function containsAny(array $values, array $needles): bool
    {
        return collect($needles)->contains(fn (string $needle): bool => in_array($needle, $values, true));
    }

    private function isUpperBunk(Bed|SleepingPlace $sleepingPlace): bool
    {
        $type = $this->value($sleepingPlace->type);
        $level = str((string) $sleepingPlace->getAttribute('bunk_level'))->lower()->toString();

        return $type === 'bunk_top' || in_array($level, ['top', 'upper', '2'], true);
    }

    private function isLowerBunk(Bed|SleepingPlace $sleepingPlace): bool
    {
        $type = $this->value($sleepingPlace->type);
        $level = str((string) $sleepingPlace->getAttribute('bunk_level'))->lower()->toString();

        return $type === 'bunk_bottom' || in_array($level, ['bottom', 'lower', '1'], true);
    }

    private function hasWorkspace(Room $room, Bed|SleepingPlace $sleepingPlace): bool
    {
        return (bool) ($room->has_desk || $room->has_chair || $room->can_work_at_night || $this->bool($sleepingPlace, 'has_lamp') || $this->bool($sleepingPlace, 'has_power_socket'));
    }

    /** @param list<string> $rules */
    private function hasQuietFit(Room $room, array $rules): bool
    {
        return $this->containsAny($rules, ['quiet_hours'])
            || in_array($room->noise_level, ['quiet', 'low'], true);
    }

    private function hasPetAllergy(GuestPreference $preferences): bool
    {
        return str((string) $preferences->allergies)
            ->lower()
            ->contains(['pet', 'pets', 'animal', 'cat', 'dog']);
    }

    private function roomGender(Room $room): ?string
    {
        return $this->value($room->gender_policy) ?: $this->value($room->gender_type);
    }

    private function roomCapacity(Room $room): ?int
    {
        return $room->max_guests ?: $room->capacity;
    }

    private function price(Bed|SleepingPlace $sleepingPlace): float
    {
        return (float) ($sleepingPlace->getAttribute('base_price_per_night') ?: $sleepingPlace->getAttribute('price_per_night') ?: 0);
    }

    private function currency(Bed|SleepingPlace $sleepingPlace, GuestPreference $preferences): string
    {
        return (string) ($sleepingPlace->getAttribute('currency') ?: $preferences->preferred_currency ?: 'EUR');
    }
}
