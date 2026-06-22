<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\ComplaintAction;
use App\Models\ComplaintCase;

class CleaningComplaintIntegrationService
{
    public function createCleaningFromComplaint(ComplaintCase $case): CleaningTask
    {
        return app(CleaningTaskService::class)->createAfterComplaint($case);
    }

    public function markComplaintActionCompleted(CleaningTask $task): void
    {
        if (! $task->complaint_case_id) {
            return;
        }

        ComplaintAction::query()
            ->where('complaint_case_id', $task->complaint_case_id)
            ->where('action_type', 'create_cleaning')
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
    }
}
