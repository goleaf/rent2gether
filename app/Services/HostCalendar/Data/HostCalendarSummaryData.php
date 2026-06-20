<?php

namespace App\Services\HostCalendar\Data;

final readonly class HostCalendarSummaryData
{
    public function __construct(
        public int $checkInsCount = 0,
        public int $checkOutsCount = 0,
        public int $cleaningsCount = 0,
        public int $repairsCount = 0,
        public int $payoutsCount = 0,
        public int $occupiedPlaces = 0,
        public int $totalPlaces = 0,
        public int $occupancyPercent = 0,
    ) {}
}
