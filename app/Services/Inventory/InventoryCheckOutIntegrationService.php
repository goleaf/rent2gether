<?php

namespace App\Services\Inventory;

use App\Models\BookingCheckOut;
use App\Models\InventoryCheck;
use App\Models\InventoryIssue;
use Illuminate\Support\Collection;

class InventoryCheckOutIntegrationService
{
    public function createReturnChecklistForCheckout(BookingCheckOut $checkOut): InventoryCheck
    {
        $check = app(InventoryCheckService::class)->createForCheckOut($checkOut);
        app(InventoryCheckItemService::class)->createExpectedItems($check);
        $this->syncCheckoutInventoryFlags($checkOut);

        return $check->refresh();
    }

    public function syncCheckoutInventoryFlags(BookingCheckOut $checkOut): void
    {
        $assignments = $checkOut->booking->inventoryAssignments()->with('inventoryItem:id,item_type')->get();
        $returnedTypes = $assignments->where('status', 'returned')->pluck('inventoryItem.item_type')->filter()->all();

        $checkOut->forceFill([
            'keys_returned' => in_array('key', $returnedTypes, true),
            'access_card_returned' => in_array('access_card', $returnedTypes, true),
            'bedding_returned' => in_array('bedding_set', $returnedTypes, true),
            'towel_returned' => in_array('towel', $returnedTypes, true),
            'locker_cleared' => in_array('locker', $returnedTypes, true) || in_array('locker_lock', $returnedTypes, true),
            'has_inventory_issue' => $assignments->whereIn('status', ['not_returned', 'lost', 'returned_damaged', 'disputed'])->isNotEmpty(),
        ])->save();
    }

    /**
     * @return Collection<int, InventoryIssue>
     */
    public function createCheckoutIssuesFromMissingItems(BookingCheckOut $checkOut): Collection
    {
        $check = $this->createReturnChecklistForCheckout($checkOut);

        return app(InventoryCheckItemService::class)->createIssuesFromFailedCheckItems($check);
    }
}
