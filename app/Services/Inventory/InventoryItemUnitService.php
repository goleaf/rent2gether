<?php

namespace App\Services\Inventory;

use App\Models\Booking;
use App\Models\InventoryItem;
use App\Models\InventoryItemUnit;
use App\Models\User;
use Illuminate\Support\Collection;

class InventoryItemUnitService
{
    /**
     * @return Collection<int, InventoryItemUnit>
     */
    public function createUnits(InventoryItem $item, int $count): Collection
    {
        $start = $item->units()->count();
        $units = collect();

        for ($i = 1; $i <= $count; $i++) {
            $units->push(InventoryItemUnit::query()->create([
                'inventory_item_id' => $item->id,
                'unit_number' => sprintf('%s-U%03d', $item->inventory_number, $start + $i),
                'status' => 'available',
                'condition_status' => $item->condition_status,
                'current_location_type' => $item->current_location_type,
                'current_location_note' => $item->current_location_note,
            ]));
        }

        return $units;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUnit(InventoryItemUnit $unit, array $data): InventoryItemUnit
    {
        $unit->fill($data)->save();

        return $unit->refresh();
    }

    public function markUnitIssued(InventoryItemUnit $unit, Booking $booking, User $guest): InventoryItemUnit
    {
        $unit->forceFill([
            'status' => 'issued_to_guest',
            'current_location_type' => 'guest',
            'assigned_booking_id' => $booking->id,
            'assigned_guest_user_id' => $guest->id,
            'last_issued_at' => now(),
        ])->save();

        return $unit->refresh();
    }

    public function markUnitReturned(InventoryItemUnit $unit): InventoryItemUnit
    {
        $unit->forceFill([
            'status' => 'available',
            'current_location_type' => 'storage',
            'assigned_booking_id' => null,
            'assigned_guest_user_id' => null,
            'last_returned_at' => now(),
        ])->save();

        return $unit->refresh();
    }

    public function markUnitLost(InventoryItemUnit $unit): InventoryItemUnit
    {
        $unit->forceFill([
            'status' => 'lost',
            'current_location_type' => 'lost',
        ])->save();

        return $unit->refresh();
    }

    public function markUnitDamaged(InventoryItemUnit $unit): InventoryItemUnit
    {
        $unit->forceFill([
            'status' => 'damaged',
            'condition_status' => 'damaged',
        ])->save();

        return $unit->refresh();
    }
}
