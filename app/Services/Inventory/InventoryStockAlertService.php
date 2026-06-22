<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryStockAlert;

class InventoryStockAlertService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createAlert(InventoryItem $item, string $alertType, array $data = []): InventoryStockAlert
    {
        $alert = InventoryStockAlert::query()->create([
            'inventory_item_id' => $item->id,
            'host_user_id' => $item->host_user_id,
            'property_id' => $item->property_id,
            'room_id' => $item->room_id,
            'sleeping_place_id' => $item->sleeping_place_id,
            'alert_type' => $alertType,
            'status' => $data['status'] ?? 'active',
            'threshold_quantity' => $data['threshold_quantity'] ?? null,
            'current_quantity' => $data['current_quantity'] ?? $item->quantity,
            'message_key' => $data['message_key'] ?? null,
        ]);

        app(InventoryEventService::class)->recordForItem($item, 'stock_alert_created', ['source_id' => $alert->id]);

        return $alert->refresh();
    }

    public function resolveAlert(InventoryStockAlert $alert): InventoryStockAlert
    {
        $alert->forceFill(['status' => 'resolved', 'resolved_at' => now()])->save();

        return $alert->refresh();
    }

    public function ignoreAlert(InventoryStockAlert $alert): InventoryStockAlert
    {
        $alert->forceFill(['status' => 'ignored'])->save();

        return $alert->refresh();
    }

    public function checkLowStock(InventoryItem $item): ?InventoryStockAlert
    {
        if (! $item->is_consumable || (float) $item->quantity > 1.0) {
            return null;
        }

        return $this->createAlert($item, (float) $item->quantity <= 0.0 ? 'out_of_stock' : 'low_stock');
    }
}
