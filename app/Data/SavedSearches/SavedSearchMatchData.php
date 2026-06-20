<?php

namespace App\Data\SavedSearches;

class SavedSearchMatchData
{
    public function __construct(
        public readonly int $sleepingPlaceId,
        public readonly int $propertyId,
        public readonly int $roomId,
        public readonly int $matchScore,
        public readonly float $pricePerNight,
        public readonly ?float $totalPrice,
        public readonly float $deposit,
        public readonly bool $isAvailable,
    ) {}
}
