<?php

namespace App\Services\Compatibility;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomCompatibilityProfile;
use BackedEnum;

class RoomCompatibilityProfileService
{
    public function syncFromRoom(Room $room): RoomCompatibilityProfile
    {
        return RoomCompatibilityProfile::query()->updateOrCreate(
            ['room_id' => $room->id],
            $this->buildFromRoom($room),
        );
    }

    public function refreshForProperty(Property $property): int
    {
        $count = 0;

        $property->rooms()
            ->select(['id', 'property_id'])
            ->chunkById(100, function ($rooms) use (&$count): void {
                foreach ($rooms as $room) {
                    $this->syncFromRoom($room);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildFromRoom(Room $room): array
    {
        $room->loadMissing('property:id,rules,amenities');
        $propertyRules = $this->values($room->property, 'rules');
        $propertyAmenities = $this->values($room->property, 'amenities');
        $maxPeople = $this->int($room, 'sleeping_places_count')
            ?: $this->int($room, 'max_guests')
            ?: $this->int($room, 'capacity');

        return [
            'gender_policy' => $this->value($room->gender_policy ?: $room->gender_type),
            'is_private' => $room->is_private,
            'is_shared' => $room->is_shared,
            'max_people_in_room' => $maxPeople ? min(255, $maxPeople) : null,
            'current_people_count' => $this->tiny($this->int($room, 'current_guests_count')),
            'typical_people_count' => $this->tiny($this->int($room, 'sleeping_places_count') ?: $maxPeople),
            'noise_level' => $room->noise_level,
            'light_level' => $room->light_level,
            'quiet_hours_enabled' => $this->containsAny($propertyRules, ['quiet_hours', 'quiet_hours_after_22', 'no_parties']),
            'quiet_hours_start' => $this->containsAny($propertyRules, ['quiet_hours_after_22']) ? '22:00' : null,
            'quiet_hours_end' => null,
            'can_turn_light_at_night' => $room->can_turn_light_at_night,
            'can_work_at_night' => $room->can_work_at_night,
            'can_eat' => $room->can_eat,
            'can_store_food' => $this->containsAny($propertyAmenities, ['kitchen', 'fridge', 'fridge_shelf']),
            'has_workspace' => (bool) ($room->has_desk || ($room->has_chair && $room->has_window)),
            'has_desk' => $room->has_desk,
            'has_chair' => $room->has_chair,
            'has_personal_lockers' => $room->sleepingPlaces()->where('has_locker', true)->exists(),
            'has_lock' => $room->has_lock,
            'has_window' => $room->has_window,
            'has_air_conditioning' => (bool) ($room->has_air_conditioning || $room->has_ac),
            'has_heating' => $room->has_heating,
            'can_open_window' => $room->has_window,
            'smoking_allowed' => ! $this->containsAny($propertyRules, ['no_smoking', 'strict_no_smoking']),
            'pets_present' => $this->containsAny($propertyRules, ['pets_present']),
            'pets_allowed' => ! $this->containsAny($propertyRules, ['no_pets']),
            'kitchen_night_use_allowed' => $this->containsAny($propertyRules, ['kitchen_night_allowed']),
            'washing_machine_available' => $this->containsAny($propertyAmenities, ['washing_machine', 'washer', 'laundry']),
            'long_stay_allowed' => (bool) ($room->is_for_long_stay ?? true),
            'short_stay_allowed' => (bool) ($room->is_for_short_stay ?? true),
            'late_entry_allowed' => $this->containsAny($propertyAmenities, ['self_check_in', 'key_safe', 'electronic_lock', '24_7_access']),
        ];
    }

    public function updateAfterRoomChange(Room $room): RoomCompatibilityProfile
    {
        $profile = $this->syncFromRoom($room);
        app(CompatibilityCacheService::class)->forgetForRoom($room);

        return $profile;
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return is_string($value) ? $value : null;
    }

    private function int(Room $room, string $attribute): ?int
    {
        $value = $room->getAttribute($attribute);

        return is_numeric($value) ? (int) $value : null;
    }

    private function tiny(?int $value): ?int
    {
        return $value === null ? null : min(255, max(0, $value));
    }

    /**
     * @return list<string>
     */
    private function values(?Property $property, string $attribute): array
    {
        $value = $property?->getAttribute($attribute);

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn (mixed $item): string => str((string) $item)->lower()->replace(' ', '_')->toString())
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $values
     * @param  list<string>  $needles
     */
    private function containsAny(array $values, array $needles): bool
    {
        return collect($needles)->contains(fn (string $needle): bool => in_array($needle, $values, true));
    }
}
