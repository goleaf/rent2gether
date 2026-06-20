<?php

namespace App\Services\HostBulk;

use App\Models\HostCleaningTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class HostBulkCleaningService
{
    public function __construct(
        private readonly HostBulkCalendarService $calendar,
    ) {}

    public function createCleaningTasks(Collection $targets, array $data): Collection
    {
        return $targets
            ->map(fn ($target): ?HostCleaningTask => $this->createTaskForTarget($target, $data))
            ->filter()
            ->values();
    }

    public function markNeedsCleaning(Collection $targets, array $data): array
    {
        $tasks = $this->createCleaningTasks($targets, ['status' => 'needed', ...$data]);

        return [
            'selected_count' => $targets->count(),
            'affected_count' => $tasks->count(),
            'skipped_count' => max(0, $targets->count() - $tasks->count()),
            'failed_count' => 0,
        ];
    }

    public function markCleaningDone(Collection $tasks): array
    {
        $affected = 0;

        foreach ($tasks as $task) {
            if ($task instanceof HostCleaningTask) {
                $task->forceFill(['status' => 'done', 'completed_at' => now()])->save();
                $affected++;
            }
        }

        return [
            'selected_count' => $tasks->count(),
            'affected_count' => $affected,
            'skipped_count' => max(0, $tasks->count() - $affected),
            'failed_count' => 0,
        ];
    }

    public function blockCalendarForCleaning(Collection $places, array $range): array
    {
        return $this->calendar->closeDates($places, $range, 'cleaning');
    }

    private function createTaskForTarget(mixed $target, array $data): ?HostCleaningTask
    {
        $status = $data['status'] ?? 'planned';

        if ($target instanceof SleepingPlace) {
            return HostCleaningTask::query()->create([
                'user_id' => $data['user_id'] ?? $target->property?->host_user_id,
                'property_id' => $target->property_id,
                'room_id' => $target->room_id,
                'sleeping_place_id' => $target->id,
                'booking_id' => $data['booking_id'] ?? null,
                'status' => $status,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
        }

        if ($target instanceof Room) {
            return HostCleaningTask::query()->create([
                'user_id' => $data['user_id'] ?? $target->property?->host_user_id,
                'property_id' => $target->property_id,
                'room_id' => $target->id,
                'status' => $status,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
        }

        if ($target instanceof Property) {
            return HostCleaningTask::query()->create([
                'user_id' => $data['user_id'] ?? $target->host_user_id,
                'property_id' => $target->id,
                'status' => $status,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
        }

        return null;
    }
}
