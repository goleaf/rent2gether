<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;

class ComplaintRatingIntegrationService
{
    public function __construct(
        private readonly ComplaintEventService $events,
    ) {}

    public function recordConfirmedComplaint(ComplaintCase $case): void
    {
        $this->events->record($case, 'complaint_resolved', ['rating_effect' => 'confirmed_complaint_recorded']);
    }

    public function recordResolvedComplaint(ComplaintCase $case): void
    {
        $this->events->record($case, 'complaint_resolved', ['rating_effect' => 'problem_resolution_recorded']);
    }

    public function removeRatingImpactIfRejected(ComplaintCase $case): void
    {
        if ($case->status === 'rejected') {
            $this->events->record($case, 'complaint_closed', ['rating_effect' => 'removed_after_rejection']);
        }
    }
}
