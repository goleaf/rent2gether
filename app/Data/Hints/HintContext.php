<?php

namespace App\Data\Hints;

use App\Data\Occupants\DateRange;

final readonly class HintContext
{
    public function __construct(
        public ?string $checkInDate = null,
        public ?string $checkOutDate = null,
        public ?int $nightsCount = null,
        public ?int $userId = null,
        public string $locale = 'en',
        public int $guestsCount = 1,
        public string $surface = 'card',
    ) {}

    public function hasDates(): bool
    {
        return filled($this->checkInDate) && filled($this->checkOutDate) && $this->dateRange()?->valid();
    }

    public function dateRange(): ?DateRange
    {
        if (! filled($this->checkInDate) || ! filled($this->checkOutDate)) {
            return null;
        }

        try {
            return new DateRange((string) $this->checkInDate, (string) $this->checkOutDate);
        } catch (\Throwable) {
            return null;
        }
    }

    public function nights(): ?int
    {
        if ($this->nightsCount !== null) {
            return $this->nightsCount;
        }

        return $this->dateRange()?->nightsCount;
    }
}
