<?php

namespace App\Data\Favorites;

final readonly class FavoriteAvailabilityResult
{
    /**
     * @param  list<array{check_in:string,check_out:string,nights:int}>  $nearestAvailableDates
     */
    public function __construct(
        public bool $isAvailable,
        public bool $becameUnavailable,
        public bool $becameAvailableAgain,
        public bool $partialAvailability = false,
        public array $nearestAvailableDates = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_available' => $this->isAvailable,
            'became_unavailable' => $this->becameUnavailable,
            'became_available_again' => $this->becameAvailableAgain,
            'partial_availability' => $this->partialAvailability,
            'nearest_available_dates' => $this->nearestAvailableDates,
        ];
    }
}
