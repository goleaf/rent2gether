<?php

namespace App\Services\HostCalendar\Data;

final readonly class HostCalendarOccupancyData
{
    public function __construct(
        public int $occupiedPlaces,
        public int $totalPlaces,
        public int $availablePlaces,
        public int $occupancyPercent,
    ) {}
}
