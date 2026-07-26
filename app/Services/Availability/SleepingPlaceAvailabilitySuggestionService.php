<?php

namespace App\Services\Availability;

use App\Enums\SleepingPlaceStatus;
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

    public function isRangeAvailable(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): bool
    {
        return $this->availability->isAvailable($place, $checkIn, $checkOut);
    }

    /**
     * @return Collection<int, array{check_out:string,nights:int,available:bool,reasons:list<string>,minimum_nights_override:int}>
     */
    public function checkoutCandidateAvailability(SleepingPlace $place, CarbonInterface $checkIn, int $maxNights): Collection
    {
        return $this->availability->checkoutCandidateAvailability($place, $checkIn, $maxNights);
    }

    /**
     * @return Collection<int, string>
     */
    public function blockingReasons(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availability->getBlockingReasons($place, $checkIn, $checkOut);
    }

    public function suggestSameRoomAlternatives(Room $room, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availablePlaces($room->sleepingPlaces(), $checkIn, $checkOut);
    }

    public function suggestSamePropertyAlternatives(Property $property, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availablePlaces($property->sleepingPlaces(), $checkIn, $checkOut);
    }

    public function suggestSameHostAlternatives(User|int $host, CarbonInterface $checkIn, CarbonInterface $checkOut, ?int $excludeSleepingPlaceId = null): Collection
    {
        $hostId = $host instanceof User ? $host->id : $host;

        return $this->availablePlaces(
            SleepingPlace::query()->where('user_id', $hostId),
            $checkIn,
            $checkOut,
            $excludeSleepingPlaceId,
        );
    }

    public function suggestNeighborRoomAlternatives(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        if (! $place->property_id || ! $place->room_id) {
            return collect();
        }

        return $this->availablePlaces(
            SleepingPlace::query()
                ->where('property_id', $place->property_id)
                ->where('room_id', '!=', $place->room_id),
            $checkIn,
            $checkOut,
            $place->id,
        );
    }

    public function suggestSimilarSleepingPlaces(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        $query = SleepingPlace::query()
            ->where('status', SleepingPlaceStatus::Active)
            ->whereKeyNot($place->id);

        if ($place->place_type) {
            $query->where('place_type', $place->place_type);
        }

        return $this->availablePlaces($query, $checkIn, $checkOut);
    }

    private function availablePlaces(mixed $query, CarbonInterface $checkIn, CarbonInterface $checkOut, ?int $excludeSleepingPlaceId = null): Collection
    {
        if ($excludeSleepingPlaceId !== null) {
            $query->whereKeyNot($excludeSleepingPlaceId);
        }

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
