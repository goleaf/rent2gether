<?php

namespace App\Data\Listings;

use Carbon\CarbonImmutable;

final readonly class ListingCardContext
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public ?int $userId = null,
        public string $locale = 'en',
        public string $currency = 'EUR',
        public ?string $checkInDate = null,
        public ?string $checkOutDate = null,
        public ?int $nightsCount = null,
        public ?int $calendarDaysCount = null,
        public int $guestsCount = 1,
        public ?string $source = null,
        public array $filters = [],
    ) {}

    public function hasDates(): bool
    {
        if ($this->checkInDate === null || $this->checkInDate === '' || $this->checkOutDate === null || $this->checkOutDate === '') {
            return false;
        }

        $checkIn = $this->checkIn();
        $checkOut = $this->checkOut();

        return $checkIn !== null && $checkOut !== null && $checkOut->greaterThan($checkIn);
    }

    public function checkIn(): ?CarbonImmutable
    {
        return $this->date($this->checkInDate);
    }

    public function checkOut(): ?CarbonImmutable
    {
        return $this->date($this->checkOutDate);
    }

    public function nights(): ?int
    {
        if ($this->nightsCount !== null) {
            return $this->nightsCount;
        }

        if (! $this->hasDates()) {
            return null;
        }

        return (int) $this->checkIn()?->diffInDays($this->checkOut());
    }

    public function calendarDays(): ?int
    {
        if ($this->calendarDaysCount !== null) {
            return $this->calendarDaysCount;
        }

        $nights = $this->nights();

        return $nights === null ? null : $nights + 1;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'locale' => $this->locale,
            'currency' => $this->currency,
            'check_in_date' => $this->checkInDate,
            'check_out_date' => $this->checkOutDate,
            'nights_count' => $this->nights(),
            'calendar_days_count' => $this->calendarDays(),
            'guests_count' => $this->guestsCount,
            'source' => $this->source,
            'filters' => $this->filters,
        ];
    }

    private function date(?string $date): ?CarbonImmutable
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
