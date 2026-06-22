<?php

namespace App\Services\Cleaning;

use App\Models\CleaningStatusLog;
use App\Models\CleaningTask;
use App\Models\User;

class CleaningStatusService
{
    public function transition(CleaningTask $task, string $newStatus, ?User $user = null, array $context = []): CleaningTask
    {
        $oldStatus = $task->status;

        if (! $this->canTransition($task, $newStatus)) {
            return $task->refresh();
        }

        $task->forceFill(['status' => $newStatus])->save();

        CleaningStatusLog::query()->create([
            'cleaning_task_id' => $task->id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context === [] ? null : $context,
        ]);

        return $task->refresh();
    }

    public function canTransition(CleaningTask $task, string $newStatus): bool
    {
        return $task->status !== $newStatus;
    }

    public function syncRelatedBookingStatus(CleaningTask $task): void
    {
        if ($task->booking && $task->issues_found) {
            $task->booking->forceFill(['has_complaint' => true])->save();
        }
    }

    public function syncCalendarStatus(CleaningTask $task): void
    {
        app(CleaningCalendarIntegrationService::class)->blockCalendarForCleaning($task);
    }
}
