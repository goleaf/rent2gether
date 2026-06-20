<?php

namespace App\Services\HostCalendar;

use App\Models\HostCalendarEvent;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Calendar\CalendarPriceService;
use App\Services\HostCalendar\Data\HostCalendarFilters;
use App\Services\HostCalendar\Data\HostCalendarPriceData;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HostCalendarPriceService
{
    public function __construct(
        private readonly CalendarPriceService $prices,
        private readonly HostCalendarDraftService $calendar,
        private readonly HostCalendarSnapshotService $snapshots,
        private readonly HostCalendarFilterService $filters,
    ) {}

    public function getDailyPrices(SleepingPlace $place, array $range): Collection
    {
        return collect(CarbonPeriod::create(CarbonImmutable::parse($range['start']), CarbonImmutable::parse($range['end'])->subDay()))
            ->map(fn (CarbonImmutable $date): HostCalendarPriceData => new HostCalendarPriceData(
                date: $date->toDateString(),
                price: $this->prices->getPriceForDate($place, $date),
                currency: $place->currency ?? 'EUR',
                source: $place->calendarDays()->whereDate('date', $date->toDateString())->value('source') ?? 'default',
            ));
    }

    public function getPriceEvents(User $host, array $range, HostCalendarFilters $filters): Collection
    {
        $query = HostCalendarEvent::query()
            ->where('user_id', $host->id)
            ->where('event_type', 'price')
            ->whereDate('event_date', '>=', $range['start'])
            ->whereDate('event_date', '<', $range['end'])
            ->orderBy('event_date');

        return $this->filters->apply($query, $filters)->get();
    }

    public function getDatesWithoutPrice(User $host, array $range): Collection
    {
        return SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id', 'display_name', 'base_price_per_night', 'currency'])
            ->whereHas('property', fn ($property) => $property->where('host_user_id', $host->id))
            ->where(function ($query): void {
                $query->whereNull('base_price_per_night')->orWhere('base_price_per_night', '<=', 0);
            })
            ->get();
    }

    public function getWeekendPriceChanges(User $host, array $range): Collection
    {
        return SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id', 'display_name', 'weekend_price', 'base_price_per_night', 'currency'])
            ->whereHas('property', fn ($property) => $property->where('host_user_id', $host->id))
            ->whereNotNull('weekend_price')
            ->get();
    }

    public function changePrice(User $host, SleepingPlace $place, string $date, int|float|string $price, string $currency = 'EUR'): HostCalendarEvent
    {
        $this->authorizePlace($host, $place);

        $start = CarbonImmutable::parse($date);
        $this->calendar->setPriceForDates($host, $place, [
            'start' => $start->toDateString(),
            'end' => $start->addDay()->toDateString(),
        ], $price);
        $place->calendarDays()->whereDate('date', $date)->update(['currency' => $currency]);
        $this->snapshots->refreshForSleepingPlace($place);

        return HostCalendarEvent::query()
            ->where('user_id', $host->id)
            ->where('sleeping_place_id', $place->id)
            ->where('event_type', 'price')
            ->whereDate('event_date', $date)
            ->latest('id')
            ->firstOrFail();
    }

    private function authorizePlace(User $host, SleepingPlace $place): void
    {
        $place->loadMissing('property:id,host_user_id,user_id');

        if (! $place->property?->isOwnedBy($host)) {
            throw new AuthorizationException;
        }
    }
}
