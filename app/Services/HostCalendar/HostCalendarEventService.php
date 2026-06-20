<?php

namespace App\Services\HostCalendar;

use App\Models\HostCalendarEvent;
use App\Models\User;
use App\Services\HostCalendar\Data\HostCalendarFilters;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class HostCalendarEventService
{
    public function __construct(
        private readonly HostCalendarFilterService $filters,
    ) {}

    public function createEvent(array $data): HostCalendarEvent
    {
        return HostCalendarEvent::query()->create($data);
    }

    public function deleteEventsForBooking(int $bookingId): void
    {
        HostCalendarEvent::query()
            ->where('booking_id', $bookingId)
            ->delete();
    }

    public function deleteEventsForCleaningTask(int $taskId): void
    {
        HostCalendarEvent::query()
            ->where('cleaning_task_id', $taskId)
            ->delete();
    }

    public function queryForHost(User $host, array $range, HostCalendarFilters $filters): Collection
    {
        $query = HostCalendarEvent::query()
            ->select([
                'id',
                'user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'booking_id',
                'cleaning_task_id',
                'event_type',
                'event_status',
                'event_date',
                'title_key',
                'title_params_json',
                'guest_user_id',
                'guest_display_name',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'payment_status',
                'check_in_status',
                'place_status',
                'needs_cleaning',
                'needs_inspection',
                'needs_repair',
                'price_amount',
                'currency',
                'payout_status',
                'payout_amount',
                'priority',
                'source',
                'host_note',
                'is_private',
            ])
            ->where('user_id', $host->id)
            ->whereDate('event_date', '>=', $this->dateString($range['start']))
            ->whereDate('event_date', '<', $this->dateString($range['end']))
            ->orderBy('event_date')
            ->orderByDesc('priority')
            ->orderBy('id');

        return $this->filters->apply($query, $filters)->get();
    }

    private function dateString(CarbonInterface|string $date): string
    {
        return $date instanceof CarbonInterface ? $date->toDateString() : (string) $date;
    }
}
