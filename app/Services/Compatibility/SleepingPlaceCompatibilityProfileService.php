<?php

namespace App\Services\Compatibility;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCompatibilityProfile;
use BackedEnum;

class SleepingPlaceCompatibilityProfileService
{
    public function syncFromSleepingPlace(SleepingPlace $place): SleepingPlaceCompatibilityProfile
    {
        return SleepingPlaceCompatibilityProfile::query()->updateOrCreate(
            ['sleeping_place_id' => $place->id],
            $this->buildFromSleepingPlace($place),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buildFromSleepingPlace(SleepingPlace $place): array
    {
        $place->loadMissing(['physicalDetails', 'comfortDetails', 'storageDetails', 'positionDetails']);
        $type = $this->value($place->sleeping_place_type ?: $place->type);
        $typeText = str((string) $type)->lower()->toString();
        $storage = $place->storageDetails;
        $position = $place->positionDetails;
        $comfort = $place->comfortDetails;
        $physical = $place->physicalDetails;

        return [
            'sleeping_place_type' => $type,
            'is_top_bunk' => (bool) ($place->is_top_bunk || $place->bunk_level === 'top' || str_contains($typeText, 'top')),
            'is_bottom_bunk' => (bool) ($place->is_bottom_bunk || $place->bunk_level === 'bottom' || str_contains($typeText, 'bottom')),
            'is_sofa' => str_contains($typeText, 'sofa'),
            'is_floor_mattress' => str_contains($typeText, 'floor') || str_contains($typeText, 'mattress'),
            'is_for_one_person' => $place->is_for_one_person,
            'is_for_couple' => $place->is_for_couple,
            'has_curtain' => (bool) ($place->has_curtain || $position?->has_curtain),
            'has_locker' => (bool) ($place->has_locker || $storage?->has_personal_locker),
            'locker_has_lock' => (bool) ($place->locker_has_lock || $storage?->locker_has_lock || $storage?->lock_provided),
            'has_power_socket' => (bool) ($place->has_power_socket || $position?->has_power_socket),
            'has_usb_charger' => (bool) ($place->has_usb || $position?->has_usb_charger || $position?->has_usb_c_charger),
            'has_personal_lamp' => (bool) ($place->has_lamp || $position?->has_personal_lamp),
            'has_shelf' => (bool) ($place->has_shelf || $position?->has_shelf),
            'has_luggage_space' => (bool) ($place->has_luggage_space || $storage?->has_luggage_space),
            'has_bedding' => (bool) ($place->has_bedding || $comfort?->has_bedding),
            'has_towel' => (bool) ($place->has_towel || $comfort?->has_towel),
            'privacy_level' => $position?->privacy_level ?: $place->privacy_level,
            'noise_level_near_place' => $position?->noise_level_near_place ?: $place->noise_level,
            'light_level_near_place' => $position?->light_level_near_place,
            'suitable_for_tall_person' => $physical?->suitable_for_tall_person ?? $place->suitable_for_tall_person,
            'suitable_for_heavy_person' => $physical?->suitable_for_heavy_person,
            'suitable_for_limited_mobility' => $physical?->suitable_for_limited_mobility ?? $place->suitable_for_limited_mobility,
            'min_nights' => $place->min_nights,
            'max_nights' => $place->max_nights,
            'can_extend' => $place->can_extend ?? $place->extensions_allowed,
            'instant_booking_enabled' => $place->instant_booking_enabled,
        ];
    }

    public function updateAfterSleepingPlaceChange(SleepingPlace $place): SleepingPlaceCompatibilityProfile
    {
        $profile = $this->syncFromSleepingPlace($place);
        app(CompatibilityCacheService::class)->forgetForSleepingPlace($place);

        return $profile;
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return is_string($value) ? $value : null;
    }
}
