<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;
use App\Models\InspectionTask;

class CleaningMaintenanceIntegrationService
{
    public function createCleaningAfterRepair($maintenanceRequest): CleaningTask
    {
        return app(CleaningTaskService::class)->createAfterRepair($maintenanceRequest);
    }

    public function createInspectionAfterRepair($maintenanceRequest): InspectionTask
    {
        return app(InspectionTaskService::class)->createPostRepair($maintenanceRequest);
    }

    public function createMaintenanceFromCleaningIssue(CleaningTaskIssue $issue): mixed
    {
        return app(CleaningTaskIssueService::class)->createMaintenanceIfNeeded($issue);
    }
}
