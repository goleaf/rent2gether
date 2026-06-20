<?php

namespace App\Services\HostCalendar\Data;

final readonly class HostCalendarContext
{
    public function __construct(
        public array $range,
        public HostCalendarFilters $filters,
        public string $view = 'property',
    ) {}

    public static function forRange(array $range, ?HostCalendarFilters $filters = null, string $view = 'property'): self
    {
        return new self($range, $filters ?? new HostCalendarFilters, $view);
    }
}
