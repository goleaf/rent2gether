<?php

namespace App\Data\SavedSearches;

use Carbon\CarbonImmutable;

class SavedSearchContext
{
    /**
     * @param  list<int>  $requiredAmenityIds
     * @param  list<string>  $excludedConditions
     */
    public function __construct(
        public readonly ?int $cityId = null,
        public readonly ?string $cityName = null,
        public readonly ?string $district = null,
        public readonly ?string $checkIn = null,
        public readonly ?string $checkOut = null,
        public readonly int $guestsCount = 1,
        public readonly ?float $budgetMin = null,
        public readonly ?float $budgetMax = null,
        public readonly ?string $currency = 'EUR',
        public readonly ?string $roomType = null,
        public readonly ?string $sleepingPlaceType = null,
        public readonly array $requiredAmenityIds = [],
        public readonly array $excludedConditions = [],
        public readonly bool $onlyVerifiedHosts = false,
        public readonly bool $onlyInstantBooking = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cityId: isset($data['city_id']) ? (int) $data['city_id'] : null,
            cityName: isset($data['city_name']) ? trim((string) $data['city_name']) : null,
            district: isset($data['district']) ? trim((string) $data['district']) : null,
            checkIn: isset($data['check_in_date']) ? (string) $data['check_in_date'] : null,
            checkOut: isset($data['check_out_date']) ? (string) $data['check_out_date'] : null,
            guestsCount: max(1, (int) ($data['guests_count'] ?? 1)),
            budgetMin: isset($data['budget_min']) ? (float) $data['budget_min'] : null,
            budgetMax: isset($data['budget_max']) ? (float) $data['budget_max'] : null,
            currency: isset($data['currency']) ? strtoupper((string) $data['currency']) : 'EUR',
            roomType: isset($data['room_type']) ? (string) $data['room_type'] : null,
            sleepingPlaceType: isset($data['sleeping_place_type']) ? (string) $data['sleeping_place_type'] : null,
            requiredAmenityIds: array_values(array_filter(array_map('intval', (array) ($data['required_amenity_ids'] ?? [])))),
            excludedConditions: array_values(array_filter(array_map('strval', (array) ($data['excluded_conditions'] ?? [])))),
            onlyVerifiedHosts: (bool) ($data['only_verified_hosts'] ?? false),
            onlyInstantBooking: (bool) ($data['only_instant_booking'] ?? false),
        );
    }

    public function checkInDate(): ?CarbonImmutable
    {
        return $this->date($this->checkIn);
    }

    public function checkOutDate(): ?CarbonImmutable
    {
        return $this->date($this->checkOut);
    }

    private function date(?string $date): ?CarbonImmutable
    {
        if (! $date) {
            return null;
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }
}
