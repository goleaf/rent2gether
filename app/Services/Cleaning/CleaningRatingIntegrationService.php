<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;

class CleaningRatingIntegrationService
{
    public function recordSuccessfulCleaning(CleaningTask $task): void
    {
        app(CleaningEventService::class)->record($task, 'cleaning_completed', ['rating_signal' => 'successful_cleaning']);
    }

    public function recordMissedCleaningIfConfirmed(CleaningTask $task): void
    {
        if ($task->status === 'failed') {
            app(CleaningEventService::class)->record($task, 'cleaning_failed', ['rating_signal' => 'missed_cleaning']);
        }
    }

    public function recordCleanlinessComplaintResolved(CleaningTask $task): void
    {
        app(CleaningEventService::class)->record($task, 'cleanliness_complaint_resolved');
    }
}
