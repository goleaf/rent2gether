<?php

namespace App\Data\Occupants;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

readonly class DateRange
{
    public CarbonImmutable $checkIn;

    public CarbonImmutable $checkOut;

    public int $nightsCount;

    public int $calendarDaysCount;

    public function __construct(CarbonInterface|string $checkIn, CarbonInterface|string $checkOut)
    {
        $this->checkIn = $checkIn instanceof CarbonInterface
            ? CarbonImmutable::instance($checkIn)->startOfDay()
            : CarbonImmutable::parse($checkIn)->startOfDay();

        $this->checkOut = $checkOut instanceof CarbonInterface
            ? CarbonImmutable::instance($checkOut)->startOfDay()
            : CarbonImmutable::parse($checkOut)->startOfDay();

        $this->nightsCount = max(0, (int) $this->checkIn->diffInDays($this->checkOut));
        $this->calendarDaysCount = $this->nightsCount + 1;
    }

    public function valid(): bool
    {
        return $this->checkOut->greaterThan($this->checkIn);
    }
}
