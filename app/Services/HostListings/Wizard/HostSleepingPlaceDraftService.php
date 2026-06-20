<?php

namespace App\Services\HostListings\Wizard;

use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class HostSleepingPlaceDraftService
{
    public function createSleepingPlace(Room $room, array $data): SleepingPlace
    {
        return $room->sleepingPlaces()->create($this->payload($room, $data));
    }

    public function updateSleepingPlace(SleepingPlace $place, array $data): SleepingPlace
    {
        $place->fill($this->payload($place->room, $data, $place))->save();

        return $place->refresh();
    }

    public function deleteSleepingPlace(SleepingPlace $place): void
    {
        $place->delete();
    }

    /**
     * @return Collection<int, SleepingPlace>
     */
    public function autoCreatePlacesForRoom(Room $room, int $count): Collection
    {
        $existing = $room->sleepingPlaces()->count();
        $places = collect();

        if ($existing >= $count) {
            return $places;
        }

        foreach (range($existing + 1, $count) as $number) {
            $places->push($this->createSleepingPlace($room, [
                'place_number' => (string) $number,
                'display_name' => __('listing_wizard.defaults.sleeping_place_name', ['number' => $number]),
                'sort_order' => $number,
            ]));
        }

        return $places;
    }

    public function syncPlaces(Room $room, array $places): void
    {
        foreach ($places as $placeData) {
            isset($placeData['id'])
                ? $this->updateSleepingPlace($room->sleepingPlaces()->whereKey($placeData['id'])->firstOrFail(), $placeData)
                : $this->createSleepingPlace($room, $placeData);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Room $room, array $data, ?SleepingPlace $place = null): array
    {
        $type = $data['type'] ?? $place?->type?->value ?? SleepingPlaceType::Single->value;

        return [
            'property_id' => $room->property_id,
            'type' => $type,
            'sleeping_place_type' => $data['sleeping_place_type'] ?? $type,
            'status' => $data['status'] ?? $place?->status?->value ?? SleepingPlaceStatus::Draft->value,
            'place_number' => $data['place_number'] ?? $place?->place_number,
            'display_name' => $data['display_name'] ?? $place?->display_name,
            'bunk_level' => $data['bunk_level'] ?? $place?->bunk_level,
            'base_price_per_night' => $data['base_price_per_night'] ?? $place?->base_price_per_night ?? 0,
            'currency' => $data['currency'] ?? $place?->currency ?? 'EUR',
            'has_power_socket' => $data['has_power_socket'] ?? $place?->has_power_socket ?? false,
            'has_lamp' => $data['has_lamp'] ?? $place?->has_lamp ?? false,
            'has_locker' => $data['has_locker'] ?? $place?->has_locker ?? false,
            'has_curtain' => $data['has_curtain'] ?? $place?->has_curtain ?? false,
            'has_bedding' => $data['has_bedding'] ?? $place?->has_bedding ?? true,
            'has_towel' => $data['has_towel'] ?? $place?->has_towel ?? false,
            'min_nights' => $data['min_nights'] ?? $place?->min_nights ?? 1,
            'max_nights' => $data['max_nights'] ?? $place?->max_nights,
            'instant_booking_enabled' => $data['instant_booking_enabled'] ?? $place?->instant_booking_enabled ?? false,
            'requires_host_approval' => $data['requires_host_approval'] ?? $place?->requires_host_approval ?? true,
            'publication_status' => $data['publication_status'] ?? $place?->publication_status ?? 'draft',
            'sort_order' => $data['sort_order'] ?? $place?->sort_order ?? 0,
        ];
    }
}
