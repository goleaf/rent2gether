<?php

namespace App\Services\Inventory;

use App\Models\InspectionTask;
use App\Models\InventoryCheck;

class InventoryInspectionIntegrationService
{
    public function checkInventoryDuringInspection(InspectionTask $inspection): InventoryCheck
    {
        $check = app(InventoryCheckService::class)->createForInspection($inspection);
        app(InventoryCheckItemService::class)->createExpectedItems($check);
        $this->syncInspectionInventoryResult($inspection);

        return $check->refresh();
    }

    public function syncInspectionInventoryResult(InspectionTask $inspection): void
    {
        $missingRequired = app(InventoryReadinessIntegrationService::class)->checkInventoryReadiness($inspection->sleepingPlace)['missing_required'];

        $inspection->forceFill([
            'issues_found' => $missingRequired !== [],
            'calendar_block_required' => $missingRequired !== [],
        ])->save();
    }
}
