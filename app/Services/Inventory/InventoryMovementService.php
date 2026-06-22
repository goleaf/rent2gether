<?php

namespace App\Services\Inventory;

use App\Models\Booking;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;

class InventoryMovementService
{
    public function __construct(
        private readonly InventoryNumberService $numbers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordMovement(InventoryItem $item, string $movementType, array $data): InventoryMovement
    {
        return InventoryMovement::query()->create([
            'movement_number' => $data['movement_number'] ?? $this->numbers->generateMovementNumber(),
            'inventory_item_id' => $item->id,
            'inventory_item_unit_id' => $data['inventory_item_unit_id'] ?? null,
            'booking_id' => $data['booking_id'] ?? null,
            'booking_inventory_assignment_id' => $data['booking_inventory_assignment_id'] ?? null,
            'from_location_type' => $data['from_location_type'] ?? $item->current_location_type,
            'from_location_note' => $data['from_location_note'] ?? $item->current_location_note,
            'to_location_type' => $data['to_location_type'],
            'to_location_note' => $data['to_location_note'] ?? null,
            'movement_type' => $movementType,
            'quantity' => $data['quantity'] ?? 1,
            'moved_by_user_id' => $data['moved_by_user_id'] ?? null,
            'moved_at' => $data['moved_at'] ?? now(),
            'note' => $data['note'] ?? null,
        ]);
    }

    public function moveToStorage(InventoryItem $item, ?string $location = null): InventoryMovement
    {
        $item->forceFill(['current_location_type' => 'storage', 'storage_location' => $location])->save();

        return $this->recordMovement($item->refresh(), 'moved_to_storage', ['to_location_type' => 'storage', 'to_location_note' => $location]);
    }

    public function moveToRoom(InventoryItem $item, Room $room): InventoryMovement
    {
        $item->forceFill(['room_id' => $room->id, 'current_location_type' => 'room'])->save();

        return $this->recordMovement($item->refresh(), 'moved_to_room', ['to_location_type' => 'room']);
    }

    public function moveToSleepingPlace(InventoryItem $item, SleepingPlace $place): InventoryMovement
    {
        $item->forceFill([
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'current_location_type' => 'sleeping_place',
        ])->save();

        return $this->recordMovement($item->refresh(), 'moved_to_sleeping_place', ['to_location_type' => 'sleeping_place']);
    }

    public function moveToGuest(InventoryItem $item, Booking $booking, User $guest): InventoryMovement
    {
        $item->forceFill(['status' => 'issued_to_guest', 'current_location_type' => 'guest', 'last_issued_at' => now()])->save();

        return $this->recordMovement($item->refresh(), 'issued_to_guest', [
            'booking_id' => $booking->id,
            'to_location_type' => 'guest',
            'moved_by_user_id' => $guest->id,
        ]);
    }

    public function moveToLaundry(InventoryItem $item): InventoryMovement
    {
        $item->forceFill(['status' => 'needs_washing', 'current_location_type' => 'laundry'])->save();

        return $this->recordMovement($item->refresh(), 'sent_to_laundry', ['to_location_type' => 'laundry']);
    }

    public function moveToRepair(InventoryItem $item): InventoryMovement
    {
        $item->forceFill(['status' => 'under_repair', 'current_location_type' => 'repair'])->save();

        return $this->recordMovement($item->refresh(), 'sent_to_repair', ['to_location_type' => 'repair']);
    }

    public function moveFromRepair(InventoryItem $item): InventoryMovement
    {
        $item->forceFill(['status' => 'available', 'current_location_type' => 'storage', 'last_repaired_at' => now()])->save();

        return $this->recordMovement($item->refresh(), 'returned_from_repair', ['to_location_type' => 'storage']);
    }
}
