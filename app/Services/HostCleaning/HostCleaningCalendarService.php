<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTask;
use App\Models\SleepingPlaceCalendarDay;
use App\Services\HostCalendar\HostCalendarSnapshotService;
use Carbon\CarbonInterface;

class HostCleaningCalendarService
{
    public function __construct(
        private readonly HostCleaningReadinessService $readiness,
        private readonly HostCalendarSnapshotService $snapshots,
    ) {}

    public function blockCalendarForCleaning(HostCleaningTask $task): void
    {
        if (! $task->sleeping_place_id || ! $task->scheduled_date) {
            $this->syncHostCalendarEvent($task);

            return;
        }

        $date = $this->dateString($task->scheduled_date);

        $existing = SleepingPlaceCalendarDay::query()
            ->where('sleeping_place_id', $task->sleeping_place_id)
            ->whereDate('date', $date)
            ->first();

        if (! $existing || ! in_array($existing->status, ['booked', 'repair', 'blocked', 'unavailable'], true)) {
            SleepingPlaceCalendarDay::query()->updateOrCreate(
                [
                    'sleeping_place_id' => $task->sleeping_place_id,
                    'date' => $date,
                ],
                [
                    'status' => 'cleaning',
                    'reason' => $task->reason ?: 'cleaning',
                    'source' => 'cleaning_task',
                    'booking_id' => $task->booking_id,
                    'blocked_by_host' => false,
                ],
            );
        }

        $this->syncHostCalendarEvent($task);
    }

    public function releaseCalendarAfterCleaning(HostCleaningTask $task): void
    {
        if (! $task->sleeping_place_id || ! $task->scheduled_date) {
            $this->syncHostCalendarEvent($task);

            return;
        }

        if (! $this->readiness->canMarkPlaceReady($task)) {
            if ($task->needs_repair && $task->sleeping_place_id && $task->scheduled_date) {
                $date = $this->dateString($task->scheduled_date);
                SleepingPlaceCalendarDay::query()->updateOrCreate(
                    [
                        'sleeping_place_id' => $task->sleeping_place_id,
                        'date' => $date,
                    ],
                    [
                        'status' => 'repair',
                        'reason' => 'repair',
                        'source' => 'cleaning_finding',
                        'booking_id' => $task->booking_id,
                    ],
                );
            }

            $this->syncHostCalendarEvent($task);

            return;
        }

        $date = $this->dateString($task->scheduled_date);

        $existing = SleepingPlaceCalendarDay::query()
            ->where('sleeping_place_id', $task->sleeping_place_id)
            ->whereDate('date', $date)
            ->first();

        if ($existing && in_array($existing->status, ['booked', 'repair', 'blocked', 'unavailable'], true)) {
            $this->syncHostCalendarEvent($task);

            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $task->sleeping_place_id,
                'date' => $date,
            ],
            [
                'status' => 'available',
                'reason' => 'cleaning_done',
                'source' => 'cleaning_task',
                'booking_id' => null,
                'blocked_by_host' => false,
            ],
        );

        $this->syncHostCalendarEvent($task);
    }

    public function syncHostCalendarEvent(HostCleaningTask $task): void
    {
        $this->snapshots->refreshForCleaningTask($task->fresh() ?: $task);
    }

    public function markPlaceReadyIfAllowed(HostCleaningTask $task): void
    {
        if (! $this->readiness->canMarkPlaceReady($task)) {
            return;
        }

        $task->forceFill(['place_ready_after_cleaning' => true])->save();
        $this->releaseCalendarAfterCleaning($task->fresh());
    }

    private function dateString(CarbonInterface|string|null $date): ?string
    {
        return $date instanceof CarbonInterface ? $date->toDateString() : ($date === null ? null : (string) $date);
    }
}
