<?php

namespace App\Services\HostBulk;

use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRule;
use Illuminate\Support\Collection;

class HostBulkRulesService
{
    public function updateHouseRules(Collection $properties, array $rules): array
    {
        $affected = 0;
        $existingBookings = 0;

        foreach ($properties as $property) {
            if (! $property instanceof Property) {
                continue;
            }

            $existingBookings += $property->sleepingPlaces()
                ->whereHas('bookings', fn ($query) => $query->upcoming())
                ->count();
            $property->forceFill(['rules' => array_values($rules)])->save();
            $affected++;
        }

        return [
            'selected_count' => $properties->count(),
            'affected_count' => $affected,
            'skipped_count' => 0,
            'failed_count' => 0,
            'warnings' => $existingBookings > 0 ? ['existing_bookings_should_be_notified'] : [],
            'existing_bookings_count' => $existingBookings,
        ];
    }

    public function updateRoomRules(Collection $rooms, array $rules): array
    {
        $affected = 0;

        foreach ($rooms as $room) {
            if ($room instanceof Room) {
                $room->forceFill(['rules' => array_values($rules), 'room_rules_text' => implode("\n", $rules)])->save();
                $affected++;
            }
        }

        return $this->result($rooms->count(), $affected);
    }

    public function updateSleepingPlaceRules(Collection $places, array $rules): array
    {
        $affected = 0;
        $normalizedRules = collect($rules)
            ->map(fn (mixed $rule): string => trim((string) $rule))
            ->filter()
            ->values();

        foreach ($places as $place) {
            if (! $place instanceof SleepingPlace) {
                continue;
            }

            $place->ruleRecords()->update(['status' => 'inactive']);

            foreach ($normalizedRules as $index => $ruleKey) {
                SleepingPlaceRule::query()->updateOrCreate(
                    [
                        'sleeping_place_id' => $place->id,
                        'rule_key' => $ruleKey,
                    ],
                    [
                        'rule_id' => null,
                        'sort_order' => $index + 1,
                        'status' => 'active',
                    ],
                );
            }

            $affected++;
        }

        return $this->result($places->count(), $affected);
    }

    public function updateKitchenRules(Collection $properties, array $rules): array
    {
        return $this->mergePropertyRules($properties, 'kitchen_rules', $rules);
    }

    public function updateBathroomRules(Collection $properties, array $rules): array
    {
        return $this->mergePropertyRules($properties, 'bathroom_rules', $rules);
    }

    public function updateCheckInOutTimes(Collection $properties, array $times): array
    {
        $affected = 0;

        foreach ($properties as $property) {
            if (! $property instanceof Property) {
                continue;
            }

            PropertyAccessDetail::query()->updateOrCreate(
                ['property_id' => $property->id],
                [
                    'key_pickup_method' => $times['key_pickup_method'] ?? 'self_check_in',
                    'self_check_in_available' => (bool) ($times['self_check_in_available'] ?? false),
                    'self_check_in_available_at_night' => (bool) ($times['self_check_in_available_at_night'] ?? false),
                    'access_24_7' => (bool) ($times['access_24_7'] ?? false),
                ],
            );
            $affected++;
        }

        return $this->result($properties->count(), $affected);
    }

    private function mergePropertyRules(Collection $properties, string $key, array $rules): array
    {
        $affected = 0;

        foreach ($properties as $property) {
            if (! $property instanceof Property) {
                continue;
            }

            $current = $property->rules ?? [];
            $current[$key] = array_values($rules);
            $property->forceFill(['rules' => $current])->save();
            $affected++;
        }

        return $this->result($properties->count(), $affected);
    }

    private function result(int $selected, int $affected): array
    {
        return [
            'selected_count' => $selected,
            'affected_count' => $affected,
            'skipped_count' => max(0, $selected - $affected),
            'failed_count' => 0,
        ];
    }
}
