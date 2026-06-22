<?php

namespace App\Services\Inventory;

use App\Models\InventoryIssue;
use App\Models\InventoryItem;

class InventoryMaintenanceIntegrationService
{
    public function createMaintenanceFromInventoryIssue(InventoryIssue $issue): ?object
    {
        if (! in_array($issue->issue_type, ['damaged', 'broken', 'needs_repair', 'needs_replacement'], true)) {
            return null;
        }

        $maintenanceId = $issue->maintenance_request_created_id ?: $issue->id;
        $issue->forceFill([
            'maintenance_request_created_id' => $maintenanceId,
            'status' => 'maintenance_created',
        ])->save();

        app(InventoryEventService::class)->recordForIssue($issue->refresh(), 'maintenance_created', ['source_id' => $maintenanceId]);

        return (object) [
            'id' => $maintenanceId,
            'inventory_item_id' => $issue->inventory_item_id,
            'host_user_id' => $issue->host_user_id,
            'property_id' => $issue->property_id,
            'room_id' => $issue->room_id,
            'sleeping_place_id' => $issue->sleeping_place_id,
            'description' => $issue->description,
        ];
    }

    public function markInventoryRepairedFromMaintenance(object $request): void
    {
        if (! isset($request->inventory_item_id)) {
            return;
        }

        $item = InventoryItem::query()->find($request->inventory_item_id);

        if ($item) {
            app(InventoryItemService::class)->markAvailable($item);
        }
    }

    public function markInventoryReplacedFromMaintenance(object $request): void
    {
        if (! isset($request->inventory_item_id)) {
            return;
        }

        $item = InventoryItem::query()->find($request->inventory_item_id);

        if ($item) {
            app(InventoryItemService::class)->markRetired($item);
        }
    }
}
