<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;

class CleaningSearchIntegrationService
{
    public function markPlaceRequestOnlyUntilCleaningDone(CleaningTask $task): void
    {
        app(CleaningCalendarIntegrationService::class)->blockCalendarForCleaning($task);
    }

    public function hidePlaceIfUnsafeCleaningIssue(CleaningTaskIssue $issue): void
    {
        if ($issue->issue_type === 'safety_issue_found') {
            app(CleaningCalendarIntegrationService::class)->blockCalendarForIssue($issue);
        }
    }

    public function refreshSearchIndexesAfterCleaning(CleaningTask $task): void {}
}
