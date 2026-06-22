<?php

namespace App\Services\Cleaning;

use App\Models\InspectionStatusLog;
use App\Models\InspectionTask;
use App\Models\User;

class InspectionStatusService
{
    public function transition(InspectionTask $task, string $newStatus, ?User $user = null, array $context = []): InspectionTask
    {
        $oldStatus = $task->status;

        if (! $this->canTransition($task, $newStatus)) {
            return $task->refresh();
        }

        $task->forceFill(['status' => $newStatus])->save();

        InspectionStatusLog::query()->create([
            'inspection_task_id' => $task->id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context === [] ? null : $context,
        ]);

        return $task->refresh();
    }

    public function canTransition(InspectionTask $task, string $newStatus): bool
    {
        return $task->status !== $newStatus;
    }
}
