<?php

namespace App\Services\HostCalendar;

use App\Models\Booking;
use App\Models\HostCalendarEvent;
use App\Models\HostCleaningTask;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class HostCalendarCleaningService
{
    public function __construct(
        private readonly HostCalendarSnapshotService $snapshots,
    ) {}

    public function getCleaningEvents(User $host, array $range): Collection
    {
        return HostCalendarEvent::query()
            ->where('user_id', $host->id)
            ->where('event_type', 'cleaning')
            ->whereDate('event_date', '>=', $range['start'])
            ->whereDate('event_date', '<', $range['end'])
            ->orderBy('event_date')
            ->get();
    }

    public function markNeedsCleaning(SleepingPlace $place, CarbonInterface|string $date, ?Booking $booking = null): HostCleaningTask
    {
        $place->loadMissing('property:id,host_user_id');

        $task = HostCleaningTask::query()->create([
            'user_id' => $place->property->host_user_id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking?->id,
            'status' => 'needed',
            'scheduled_date' => $date instanceof CarbonInterface ? $date->toDateString() : (string) $date,
            'reason' => 'manual',
        ]);

        $this->snapshots->refreshForCleaningTask($task);

        return $task;
    }

    public function markCleaningDone(HostCleaningTask $task): HostCleaningTask
    {
        $task->forceFill([
            'status' => 'done',
            'completed_at' => now(),
        ])->save();

        $this->snapshots->refreshForCleaningTask($task);

        return $task->refresh();
    }

    public function createCleaningAfterCheckout(Booking $booking): HostCleaningTask
    {
        $task = HostCleaningTask::query()->create([
            'user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'booking_id' => $booking->id,
            'status' => 'planned',
            'scheduled_date' => $booking->check_out_date,
            'scheduled_time' => $booking->check_out_time?->format('H:i'),
            'reason' => 'after_checkout',
        ]);

        $this->snapshots->refreshForCleaningTask($task);

        return $task;
    }
}
