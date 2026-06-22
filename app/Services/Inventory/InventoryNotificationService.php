<?php

namespace App\Services\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryIssue;
use App\Models\InventoryStockAlert;

class InventoryNotificationService
{
    public function notifyGuestItemIssued(BookingInventoryAssignment $assignment): void
    {
        app(InventoryEventService::class)->recordForAssignment($assignment, 'guest_inventory_notification_item_issued');
    }

    public function notifyGuestReturnExpected(BookingInventoryAssignment $assignment): void
    {
        app(InventoryEventService::class)->recordForAssignment($assignment, 'guest_inventory_notification_return_expected');
    }

    public function notifyHostItemNotReturned(InventoryIssue $issue): void
    {
        app(InventoryEventService::class)->recordForIssue($issue, 'host_inventory_notification_item_not_returned');
    }

    public function notifyHostItemDamaged(InventoryIssue $issue): void
    {
        app(InventoryEventService::class)->recordForIssue($issue, 'host_inventory_notification_item_damaged');
    }

    public function notifyGuestInventoryIssueCreated(InventoryIssue $issue): void
    {
        app(InventoryEventService::class)->recordForIssue($issue, 'guest_inventory_notification_issue_created');
    }

    public function notifyLowStock(InventoryStockAlert $alert): void
    {
        app(InventoryEventService::class)->recordForItem($alert->inventoryItem, 'inventory_low_stock_notification', ['source_id' => $alert->id]);
    }

    public function notifyReplacementNeeded(InventoryIssue $issue): void
    {
        app(InventoryEventService::class)->recordForIssue($issue, 'inventory_replacement_needed_notification');
    }
}
