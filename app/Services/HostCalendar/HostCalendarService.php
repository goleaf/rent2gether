<?php

namespace App\Services\HostCalendar;

use App\Models\HostCalendarEvent;
use App\Models\Property;
use App\Models\User;
use App\Services\HostCalendar\Data\HostCalendarContext;
use App\Services\HostCalendar\Data\HostCalendarDayData;
use App\Services\HostCalendar\Data\HostCalendarFilters;
use App\Services\HostCalendar\Data\HostCalendarSummaryData;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class HostCalendarService
{
    public function __construct(
        private readonly HostCalendarEventService $events,
        private readonly HostCalendarOccupancyService $occupancy,
        private readonly HostCalendarNoteService $notes,
    ) {}

    public function getCalendar(User $host, HostCalendarContext $context): array
    {
        return [
            'events' => $this->getEvents($host, $context->range, $context->filters),
            'summary' => $this->getSummary($host, $context->range, $context->filters),
        ];
    }

    public function getDayDetails(User $host, CarbonInterface|string $date, HostCalendarContext $context): HostCalendarDayData
    {
        $dateString = $date instanceof CarbonInterface ? $date->toDateString() : (string) $date;
        $range = ['start' => $dateString, 'end' => CarbonImmutable::parse($dateString)->addDay()->toDateString()];

        return new HostCalendarDayData(
            date: $dateString,
            events: $this->getEvents($host, $range, $context->filters),
            notes: $this->notes->getNotes($host, $range, $context->filters),
        );
    }

    public function getEvents(User $host, array $range, HostCalendarFilters $filters): Collection
    {
        return $this->events->queryForHost($host, $range, $filters);
    }

    public function getSummary(User $host, array $range, HostCalendarFilters $filters): HostCalendarSummaryData
    {
        $base = HostCalendarEvent::query()
            ->where('user_id', $host->id)
            ->whereDate('event_date', '>=', $range['start'])
            ->whereDate('event_date', '<', $range['end']);

        $events = $base->get(['event_type']);
        $total = Property::query()
            ->where('host_user_id', $host->id)
            ->withCount('sleepingPlaces')
            ->get()
            ->sum('sleeping_places_count');
        $occupied = $this->occupancy->getDailyOccupancy($host, $range)->max('occupied') ?? 0;

        return new HostCalendarSummaryData(
            checkInsCount: $events->where('event_type', 'check_in')->count(),
            checkOutsCount: $events->where('event_type', 'check_out')->count(),
            cleaningsCount: $events->where('event_type', 'cleaning')->count(),
            repairsCount: $events->where('event_type', 'repair')->count(),
            payoutsCount: $events->where('event_type', 'payout')->count(),
            occupiedPlaces: $occupied,
            totalPlaces: $total,
            occupancyPercent: $this->occupancy->getOccupancyPercent($occupied, $total),
        );
    }
}
