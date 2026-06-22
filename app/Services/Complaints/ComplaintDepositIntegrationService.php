<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;

class ComplaintDepositIntegrationService
{
    public function __construct(
        private readonly ComplaintActionService $actions,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
    ) {}

    public function createDepositCaseFromComplaint(ComplaintCase $case): object
    {
        $depositCaseId = $case->deposit_case_id ?: $case->id;

        $case->forceFill([
            'deposit_case_id' => $depositCaseId,
            'resolution_type' => $case->host_wants_deposit_deduction ? 'deposit_deduction' : 'deposit_return',
            'resolution_status' => 'in_progress',
        ])->save();

        $this->actions->createAction($case->fresh(), 'create_deposit_case', ['status' => 'completed', 'source_type' => 'deposit_case_future', 'source_id' => $depositCaseId, 'completed_at' => now()]);
        $this->statuses->transition($case->fresh(), 'deposit_case_created');
        $this->events->record($case->fresh(), 'deposit_case_created', ['deposit_case_id' => $depositCaseId]);

        return (object) ['id' => $depositCaseId];
    }

    public function linkDepositCase(ComplaintCase $case, mixed $depositCase): void
    {
        $case->forceFill([
            'deposit_case_id' => is_object($depositCase) && isset($depositCase->id) ? $depositCase->id : $depositCase,
        ])->save();
    }
}
