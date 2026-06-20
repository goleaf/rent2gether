<?php

namespace App\Services\HostCalendar;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\HostCalendarEvent;
use App\Models\HostCleaningTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class HostCalendarSnapshotService
{
    public function __construct(
        private readonly HostCalendarEventService $events,
    ) {}

    public function refreshForProperty(Property $property): int
    {
        $count = 0;
        Booking::query()
            ->where('property_id', $property->id)
            ->with(['guest:id,name', 'payout', 'sleepingPlace:id,property_id,room_id,display_name,currency'])
            ->get()
            ->each(function (Booking $booking) use (&$count): void {
                $count += $this->refreshForBooking($booking);
            });

        $property->sleepingPlaces()
            ->select(['id', 'property_id', 'room_id', 'display_name', 'currency', 'base_price_per_night'])
            ->cursor()
            ->each(function (SleepingPlace $place) use (&$count): void {
                $count += $this->refreshForSleepingPlace($place);
            });

        HostCleaningTask::query()
            ->where('property_id', $property->id)
            ->get()
            ->each(function (HostCleaningTask $task) use (&$count): void {
                $count += $this->refreshForCleaningTask($task);
            });

        return $count;
    }

    public function refreshForRoom(Room $room): int
    {
        $count = 0;
        Booking::query()
            ->where('room_id', $room->id)
            ->with(['guest:id,name', 'payout', 'sleepingPlace:id,property_id,room_id,display_name,currency'])
            ->get()
            ->each(function (Booking $booking) use (&$count): void {
                $count += $this->refreshForBooking($booking);
            });

        $room->sleepingPlaces()
            ->select(['id', 'property_id', 'room_id', 'display_name', 'currency', 'base_price_per_night'])
            ->cursor()
            ->each(function (SleepingPlace $place) use (&$count): void {
                $count += $this->refreshForSleepingPlace($place);
            });

        return $count;
    }

    public function refreshForSleepingPlace(SleepingPlace $place): int
    {
        $place->loadMissing('property:id,host_user_id');
        $hostId = $place->property?->host_user_id;

        if (! $hostId) {
            return 0;
        }

        HostCalendarEvent::query()
            ->where('sleeping_place_id', $place->id)
            ->where('source', 'calendar_day')
            ->delete();

        $count = 0;
        $place->calendarDays()
            ->select([
                'id',
                'sleeping_place_id',
                'date',
                'status',
                'price',
                'currency',
                'reason',
                'source',
                'booking_id',
            ])
            ->orderBy('date')
            ->get()
            ->each(function ($day) use ($place, $hostId, &$count): void {
                if ($day->price !== null) {
                    $this->events->createEvent([
                        'user_id' => $hostId,
                        'property_id' => $place->property_id,
                        'room_id' => $place->room_id,
                        'sleeping_place_id' => $place->id,
                        'event_type' => 'price',
                        'event_status' => 'active',
                        'event_date' => $this->dateString($day->date),
                        'title_key' => 'host_calendar.event_titles.price',
                        'title_params_json' => ['amount' => (float) $day->price, 'currency' => $day->currency ?? $place->currency],
                        'price_amount' => $day->price,
                        'currency' => $day->currency ?? $place->currency,
                        'priority' => 20,
                        'source' => 'calendar_day',
                        'is_private' => true,
                    ]);
                    $count++;
                }

                $eventType = match ($day->status) {
                    'repair' => 'repair',
                    'cleaning' => 'cleaning',
                    'blocked', 'unavailable' => 'blocked',
                    default => null,
                };

                if ($eventType === null) {
                    return;
                }

                $this->events->createEvent([
                    'user_id' => $hostId,
                    'property_id' => $place->property_id,
                    'room_id' => $place->room_id,
                    'sleeping_place_id' => $place->id,
                    'booking_id' => $day->booking_id,
                    'event_type' => $eventType,
                    'event_status' => $day->status,
                    'event_date' => $this->dateString($day->date),
                    'title_key' => 'host_calendar.event_titles.'.$eventType,
                    'description_key' => $day->reason ? 'host_calendar.event_descriptions.'.$day->reason : null,
                    'place_status' => $day->status,
                    'needs_cleaning' => $eventType === 'cleaning',
                    'needs_repair' => $eventType === 'repair',
                    'priority' => $eventType === 'repair' ? 80 : 40,
                    'source' => 'calendar_day',
                    'is_private' => true,
                ]);
                $count++;
            });

        return $count;
    }

    public function refreshForBooking(Booking $booking): int
    {
        $booking->loadMissing([
            'guest:id,name',
            'payout',
            'sleepingPlace:id,property_id,room_id,display_name,currency',
        ]);

        $this->deleteEventsForBooking($booking);

        if ($this->bookingIsCancelled($booking) || ! $booking->host_user_id || ! $booking->property_id || ! $booking->check_in_date || ! $booking->check_out_date) {
            return 0;
        }

        $base = [
            'user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'guest_display_name' => $booking->guest?->name,
            'check_in_date' => $this->dateString($booking->check_in_date),
            'check_out_date' => $this->dateString($booking->check_out_date),
            'nights_count' => $booking->nights_count ?: $this->nights($booking),
            'payment_status' => $this->value($booking->payment_status),
            'check_in_status' => $this->checkInStatus($booking),
            'place_status' => $this->placeStatus($booking),
            'currency' => $booking->currency ?: $booking->sleepingPlace?->currency,
            'source' => 'booking',
            'is_private' => true,
        ];

        $this->events->createEvent([
            ...$base,
            'event_type' => 'booking',
            'event_status' => $this->value($booking->status),
            'event_date' => $this->dateString($booking->check_in_date),
            'title_key' => 'host_calendar.event_titles.booking',
            'title_params_json' => ['guest' => $booking->guest?->name],
            'priority' => 50,
        ]);

        $this->events->createEvent([
            ...$base,
            'event_type' => 'check_in',
            'event_status' => $this->checkInStatus($booking),
            'event_date' => $this->dateString($booking->check_in_date),
            'title_key' => 'host_calendar.event_titles.check_in',
            'title_params_json' => ['guest' => $booking->guest?->name],
            'priority' => 90,
        ]);

        $this->events->createEvent([
            ...$base,
            'event_type' => 'check_out',
            'event_status' => $booking->checked_out_at ? 'checked_out' : 'scheduled',
            'event_date' => $this->dateString($booking->check_out_date),
            'title_key' => 'host_calendar.event_titles.check_out',
            'title_params_json' => ['guest' => $booking->guest?->name],
            'needs_cleaning' => true,
            'priority' => 90,
        ]);

        $count = 3;
        $payout = $booking->payout;

        if ($payout) {
            $this->events->createEvent([
                ...$base,
                'event_type' => 'payout',
                'event_status' => $this->value($payout->status),
                'event_date' => $this->dateString($payout->scheduled_date ?? $payout->paid_date ?? $booking->check_out_date),
                'title_key' => 'host_calendar.event_titles.payout',
                'payout_status' => $this->value($payout->status),
                'payout_amount' => $payout->net_amount,
                'currency' => $payout->currency,
                'priority' => 60,
            ]);
            $count++;
        } elseif ($booking->total_amount !== null) {
            $this->events->createEvent([
                ...$base,
                'event_type' => 'payout',
                'event_status' => 'expected',
                'event_date' => $this->dateString($booking->check_out_date),
                'title_key' => 'host_calendar.event_titles.payout',
                'payout_status' => 'expected',
                'payout_amount' => $booking->total_amount,
                'priority' => 30,
            ]);
            $count++;
        }

        return $count;
    }

    public function refreshForCleaningTask(HostCleaningTask $task): int
    {
        $task->loadMissing(['property:id,host_user_id', 'booking:id,guest_user_id']);
        $this->events->deleteEventsForCleaningTask($task->id);

        if (! $task->scheduled_date || ! $task->user_id || ! $task->property_id) {
            return 0;
        }

        $this->events->createEvent([
            'user_id' => $task->user_id,
            'property_id' => $task->property_id,
            'room_id' => $task->room_id,
            'sleeping_place_id' => $task->sleeping_place_id,
            'booking_id' => $task->booking_id,
            'cleaning_task_id' => $task->id,
            'event_type' => 'cleaning',
            'event_status' => $task->status,
            'event_date' => $this->dateString($task->scheduled_date),
            'title_key' => 'host_calendar.event_titles.cleaning',
            'description_key' => $task->reason ? 'host_calendar.event_descriptions.'.$task->reason : null,
            'needs_cleaning' => $task->status !== 'done',
            'priority' => $task->status === 'needed' ? 85 : 70,
            'source' => 'cleaning_task',
            'host_note' => $task->note,
            'is_private' => true,
        ]);

        return 1;
    }

    public function deleteEventsForBooking(Booking $booking): void
    {
        $this->events->deleteEventsForBooking($booking->id);
    }

    private function bookingIsCancelled(Booking $booking): bool
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->isCancelled()
            : str_starts_with((string) $booking->status, 'cancelled');
    }

    private function checkInStatus(Booking $booking): string
    {
        if ($booking->checked_out_at || in_array($this->value($booking->status), ['checked_out', 'completed'], true)) {
            return 'checked_out';
        }

        if ($booking->checked_in_at || in_array($this->value($booking->status), ['checked_in', 'in_progress', 'active_stay'], true)) {
            return 'checked_in';
        }

        return 'scheduled';
    }

    private function placeStatus(Booking $booking): string
    {
        return match ($this->checkInStatus($booking)) {
            'checked_out' => 'checked_out',
            'checked_in' => 'checked_in',
            default => in_array($this->value($booking->status), ['awaiting_payment', 'pending_payment', 'pending_host'], true)
                ? 'pending_confirmation'
                : 'booked',
        };
    }

    private function nights(Booking $booking): int
    {
        return (int) CarbonImmutable::parse($booking->check_in_date)->diffInDays(CarbonImmutable::parse($booking->check_out_date));
    }

    private function value(mixed $value): ?string
    {
        return $value instanceof \BackedEnum ? $value->value : ($value === null ? null : (string) $value);
    }

    private function dateString(CarbonInterface|string|null $date): ?string
    {
        return $date instanceof CarbonInterface ? $date->toDateString() : ($date === null ? null : (string) $date);
    }
}
