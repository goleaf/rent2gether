<?php

namespace App\Services\Favorites;

use App\Data\Favorites\FavoriteAvailabilityResult;
use App\Models\Favorite;
use App\Models\SleepingPlace;
use App\Services\Availability\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FavoriteAvailabilityService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function check(Favorite $favorite): FavoriteAvailabilityResult
    {
        $favorite->loadMissing(['sleepingPlace:id,room_id,property_id,status']);

        if (! $favorite->sleepingPlace instanceof SleepingPlace) {
            return $this->store($favorite, false, false, false, false, []);
        }

        $checkIn = $this->date($favorite->check_in_date ?: $favorite->check_in);
        $checkOut = $this->date($favorite->check_out_date ?: $favorite->check_out);

        if (! $checkIn || ! $checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return $this->store($favorite, true, false, false, false, []);
        }

        $wasAvailable = $favorite->is_currently_available;
        $available = $this->availability->isAvailable($favorite->sleepingPlace, $checkIn, $checkOut);
        $unavailableDates = $available
            ? []
            : $this->availability->unavailableDates($favorite->sleepingPlace, $checkIn, $checkOut);
        $nights = (int) $checkIn->diffInDays($checkOut);
        $nearest = $available ? [] : $this->findNearestAvailableDates($favorite);
        $partial = ! $available && $unavailableDates !== [] && count($unavailableDates) < max(1, $nights);

        return $this->store(
            favorite: $favorite,
            available: $available,
            becameUnavailable: $wasAvailable === true && $available === false,
            becameAvailableAgain: $wasAvailable === false && $available === true,
            partialAvailability: $partial,
            nearestAvailableDates: $nearest,
        );
    }

    /**
     * @param  Collection<int, Favorite>  $favorites
     * @return Collection<int, FavoriteAvailabilityResult>
     */
    public function checkMany(Collection $favorites): Collection
    {
        return $favorites->map(fn (Favorite $favorite): FavoriteAvailabilityResult => $this->check($favorite));
    }

    public function markUnavailable(Favorite $favorite): Favorite
    {
        $favorite->forceFill([
            'is_currently_available' => false,
            'became_unavailable' => true,
            'became_available_again' => false,
            'availability_last_checked_at' => now(),
        ])->save();

        return $favorite->refresh();
    }

    public function markAvailableAgain(Favorite $favorite): Favorite
    {
        $favorite->forceFill([
            'is_currently_available' => true,
            'became_unavailable' => false,
            'became_available_again' => true,
            'availability_last_checked_at' => now(),
        ])->save();

        return $favorite->refresh();
    }

    /**
     * @return list<array{check_in:string,check_out:string,nights:int}>
     */
    public function findNearestAvailableDates(Favorite $favorite): array
    {
        $favorite->loadMissing(['sleepingPlace:id,room_id,property_id,status']);

        if (! $favorite->sleepingPlace instanceof SleepingPlace) {
            return [];
        }

        $checkIn = $this->date($favorite->check_in_date ?: $favorite->check_in) ?: CarbonImmutable::today()->addDay();
        $checkOut = $this->date($favorite->check_out_date ?: $favorite->check_out);
        $nights = $checkOut && $checkOut->greaterThan($checkIn)
            ? (int) $checkIn->diffInDays($checkOut)
            : max(1, (int) $favorite->nights_count);

        return $this->availability->nearestAvailableRanges($favorite->sleepingPlace, $checkIn, $nights);
    }

    /**
     * @param  list<array{check_in:string,check_out:string,nights:int}>  $nearestAvailableDates
     */
    private function store(
        Favorite $favorite,
        bool $available,
        bool $becameUnavailable,
        bool $becameAvailableAgain,
        bool $partialAvailability,
        array $nearestAvailableDates,
    ): FavoriteAvailabilityResult {
        $favorite->forceFill([
            'is_currently_available' => $available,
            'became_unavailable' => $becameUnavailable,
            'became_available_again' => $becameAvailableAgain,
            'partial_availability' => $partialAvailability,
            'nearest_available_dates_json' => $nearestAvailableDates === [] ? null : $nearestAvailableDates,
            'availability_last_checked_at' => now(),
        ])->save();

        return new FavoriteAvailabilityResult(
            isAvailable: $available,
            becameUnavailable: $becameUnavailable,
            becameAvailableAgain: $becameAvailableAgain,
            partialAvailability: $partialAvailability,
            nearestAvailableDates: $nearestAvailableDates,
        );
    }

    private function date(mixed $date): ?CarbonImmutable
    {
        if ($date === null || $date === '') {
            return null;
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }
}
