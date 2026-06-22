<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTaskIssue;

class CleaningDepositIntegrationService
{
    public function createDepositEvidenceFromCleaningIssue(CleaningTaskIssue $issue): mixed
    {
        return (object) [
            'source' => 'cleaning_issue',
            'source_id' => $issue->id,
            'booking_id' => $issue->booking_id,
        ];
    }

    public function startDepositReviewIfIssueFound(CleaningTaskIssue $issue): mixed
    {
        return app(CleaningTaskIssueService::class)->createDepositReviewIfNeeded($issue);
    }
}
