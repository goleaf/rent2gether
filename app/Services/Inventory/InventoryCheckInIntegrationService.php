<?php

namespace App\Services\Inventory;

use App\Models\BookingCheckIn;
use App\Models\BookingInventoryAssignment;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;

class InventoryCheckInIntegrationService
{
    /**
     * @return Collection<int, InventoryItem>
     */
    public function prepareIssuedItemsForCheckIn(BookingCheckIn $checkIn): Collection
    {
        return InventoryItem::query()
            ->where('property_id', $checkIn->property_id)
            ->where('sleeping_place_id', $checkIn->sleeping_place_id)
            ->whereIn('item_type', ['key', 'access_card', 'door_code', 'intercom_code', 'bedding_set', 'towel', 'locker', 'locker_lock'])
            ->whereNotIn('status', ['issued_to_guest', 'lost', 'missing', 'damaged', 'retired', 'disposed'])
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, BookingInventoryAssignment>
     */
    public function issueDefaultItemsAtCheckIn(BookingCheckIn $checkIn): Collection
    {
        $issued = $this->prepareIssuedItemsForCheckIn($checkIn)
            ->map(fn (InventoryItem $item) => app(InventoryAssignmentService::class)->issueToGuest($checkIn->host, $checkIn->booking, $item, [
                'booking_check_in_id' => $checkIn->id,
                'assignment_type' => 'issued_at_check_in',
                'expected_return' => $item->is_returnable,
            ]));

        $this->syncCheckInIssuedFlags($checkIn);

        return $issued->values();
    }

    public function syncCheckInIssuedFlags(BookingCheckIn $checkIn): void
    {
        $assignments = $checkIn->booking->inventoryAssignments()->with('inventoryItem:id,item_type')->get();
        $itemTypes = $assignments->pluck('inventoryItem.item_type')->filter()->all();

        $checkIn->forceFill([
            'keys_handed_over' => in_array('key', $itemTypes, true) || in_array('access_card', $itemTypes, true),
            'bedding_issued' => in_array('bedding_set', $itemTypes, true),
            'towel_issued' => in_array('towel', $itemTypes, true),
            'locker_assigned' => in_array('locker', $itemTypes, true) || in_array('locker_lock', $itemTypes, true),
        ])->save();
    }
}
