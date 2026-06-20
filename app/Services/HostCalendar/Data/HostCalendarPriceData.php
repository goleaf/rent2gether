<?php

namespace App\Services\HostCalendar\Data;

final readonly class HostCalendarPriceData
{
    public function __construct(
        public string $date,
        public float $price,
        public string $currency,
        public string $source,
    ) {}
}
