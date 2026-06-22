<?php

namespace App\Services\Inventory;

use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\InventoryConsumableUsage;
use App\Models\InventoryItem;
use App\Models\User;

class InventoryConsumableUsageService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function recordUsage(User $user, InventoryItem $item, array $data): InventoryConsumableUsage
    {
        return InventoryConsumableUsage::query()->create([
            'inventory_item_id' => $item->id,
            'host_user_id' => $item->host_user_id,
            'property_id' => $item->property_id,
            'room_id' => $item->room_id,
            'sleeping_place_id' => $item->sleeping_place_id,
            'booking_id' => $data['booking_id'] ?? null,
            'cleaning_task_id' => $data['cleaning_task_id'] ?? null,
            'inspection_task_id' => $data['inspection_task_id'] ?? null,
            'usage_type' => $data['usage_type'],
            'quantity_used' => $data['quantity_used'] ?? 1,
            'unit' => $data['unit'] ?? $item->unit,
            'used_by_user_id' => $user->id,
            'used_at' => $data['used_at'] ?? now(),
            'note' => $data['note'] ?? null,
        ]);
    }

    public function recordCleaningUsage(CleaningTask $task, InventoryItem $item, float $quantity): InventoryConsumableUsage
    {
        return $this->recordUsage($task->host, $item, [
            'booking_id' => $task->booking_id,
            'cleaning_task_id' => $task->id,
            'usage_type' => 'cleaning',
            'quantity_used' => $quantity,
        ]);
    }

    public function recordGuestProvided(Booking $booking, InventoryItem $item, float $quantity): InventoryConsumableUsage
    {
        return $this->recordUsage($booking->host, $item, [
            'booking_id' => $booking->id,
            'usage_type' => 'guest_provided',
            'quantity_used' => $quantity,
        ]);
    }
}
