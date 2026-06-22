<?php

namespace App\Services\Complaints;

use App\Models\ComplaintAction;
use App\Models\ComplaintCase;

class ComplaintActionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createAction(ComplaintCase $case, string $actionType, array $data = []): ComplaintAction
    {
        return ComplaintAction::query()->create([
            'complaint_case_id' => $case->id,
            'action_type' => $actionType,
            'status' => $data['status'] ?? 'pending',
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'assigned_to_user_id' => $data['assigned_to_user_id'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'created_by_user_id' => $data['created_by_user_id'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
            'note' => $data['note'] ?? null,
        ]);
    }

    public function markActionInProgress(ComplaintAction $action): ComplaintAction
    {
        $action->forceFill(['status' => 'in_progress'])->save();

        return $action->fresh();
    }

    public function markActionCompleted(ComplaintAction $action): ComplaintAction
    {
        $action->forceFill(['status' => 'completed', 'completed_at' => now()])->save();

        return $action->fresh();
    }

    public function markActionFailed(ComplaintAction $action, string $reason): ComplaintAction
    {
        $action->forceFill(['status' => 'failed', 'note' => $reason])->save();

        return $action->fresh();
    }
}
