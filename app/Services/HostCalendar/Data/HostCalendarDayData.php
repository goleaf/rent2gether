<?php

namespace App\Services\HostCalendar\Data;

use Illuminate\Support\Collection;

final readonly class HostCalendarDayData
{
    public function __construct(
        public string $date,
        public Collection $events,
        public Collection $notes,
    ) {}
}
