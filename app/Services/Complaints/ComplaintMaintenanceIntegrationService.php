<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;

class ComplaintMaintenanceIntegrationService
{
    public function __construct(
        private readonly ComplaintActionService $actions,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
    ) {}

    public function createMaintenanceFromComplaint(ComplaintCase $case): object
    {
        $maintenanceId = $case->maintenance_request_id ?: $case->id;

        $case->forceFill([
            'maintenance_request_id' => $maintenanceId,
            'resolution_type' => 'repair',
            'resolution_status' => 'in_progress',
        ])->save();

        $this->actions->createAction($case->fresh(), 'create_maintenance', [
            'status' => 'completed',
            'source_type' => 'maintenance_request_future',
            'source_id' => $maintenanceId,
            'completed_at' => now(),
        ]);
        $this->statuses->transition($case->fresh(), 'maintenance_created');
        $this->events->record($case->fresh(), 'maintenance_created', ['maintenance_request_id' => $maintenanceId]);

        return (object) ['id' => $maintenanceId];
    }

    public function markMaintenanceResolvedComplaint(ComplaintCase $case): void
    {
        $this->statuses->transition($case, 'resolved');
        $this->events->record($case->fresh(), 'complaint_resolved', ['resolution_type' => 'repair']);
    }
}
