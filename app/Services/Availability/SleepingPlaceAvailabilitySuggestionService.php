<?php

namespace App\Services\Availability;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SleepingPlaceAvailabilitySuggestionService
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {}

    public function suggestNearestAvailableRanges(SleepingPlace $place, CarbonInterface $preferredCheckIn, int $nights): Collection
    {
        return collect($this->availability->nearestAvailableRanges($place, $preferredCheckIn, $nights));
    }

    public function suggestAvailableCheckOutDates(SleepingPlace $place, CarbonInterface $checkIn): Collection
    {
        $dates = collect();
        $checkIn = CarbonImmutable::instance($checkIn)->startOfDay();

        for ($nights = 1; $nights <= 30; $nights++) {
            $checkOut = $checkIn->addDays($nights);

            if ($this->availability->isAvailable($place, $checkIn, $checkOut)) {
                $dates->push([
                    'check_out' => $checkOut->toDateString(),
                    'nights' => $nights,
                ]);
            }

            if ($dates->count() >= 10) {
                break;
            }
        }

        return $dates;
    }

    public function suggestSameRoomAlternatives(Room $room, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availablePlaces($room->sleepingPlaces(), $checkIn, $checkOut);
    }

    public function suggestSamePropertyAlternatives(Property $property, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availablePlaces($property->sleepingPlaces(), $checkIn, $checkOut);
    }

    public function suggestSameHostAlternatives(User $host, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availablePlaces(SleepingPlace::query()->where('user_id', $host->id), $checkIn, $checkOut);
    }

    private function availablePlaces(mixed $query, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $query
            ->select(['id', 'room_id', 'property_id', 'user_id', 'title', 'display_name', 'base_price', 'base_price_per_night', 'currency', 'status'])
            ->orderBy('sort_order')
            ->limit(12)
            ->get()
            ->filter(fn (SleepingPlace $place): bool => $this->availability->isAvailable($place, $checkIn, $checkOut))
            ->take(3)
            ->map(fn (SleepingPlace $place): array => [
                'id' => $place->id,
                'title' => $place->title ?? $place->display_name,
                'room_id' => $place->room_id,
                'property_id' => $place->property_id,
                'price' => $place->base_price ?? $place->base_price_per_night,
                'currency' => $place->currency ?? 'EUR',
            ])
            ->values();
    }
}
