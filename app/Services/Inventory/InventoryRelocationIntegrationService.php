<?php

namespace App\Services\Inventory;

use App\Models\BookingRelocation;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;

class InventoryRelocationIntegrationService
{
    /**
     * @return Collection<int, InventoryItem>
     */
    public function prepareTransferForRelocation(BookingRelocation $relocation): Collection
    {
        return InventoryItem::query()
            ->where('property_id', $relocation->current_property_id)
            ->where('sleeping_place_id', $relocation->current_sleeping_place_id)
            ->orderBy('id')
            ->get();
    }

    public function markOldItemsReturned(BookingRelocation $relocation): void
    {
        $this->prepareTransferForRelocation($relocation)
            ->each(fn (InventoryItem $item) => app(InventoryItemService::class)->markAvailable($item));
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    public function issueNewItemsForRelocation(BookingRelocation $relocation): Collection
    {
        return InventoryItem::query()
            ->where('property_id', $relocation->new_property_id)
            ->where('sleeping_place_id', $relocation->new_sleeping_place_id)
            ->orderBy('id')
            ->get();
    }
}
