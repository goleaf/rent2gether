<?php

namespace App\Services\Inventory;

use App\Models\Booking;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class InventoryListingIntegrationService
{
    public function syncListingPromisesFromInventory(SleepingPlace $place): void
    {
        $promisedTypes = InventoryItem::query()
            ->where('sleeping_place_id', $place->id)
            ->where('is_promised_in_listing', true)
            ->pluck('item_type')
            ->all();

        $place->forceFill([
            'has_bedding' => in_array('bedding_set', $promisedTypes, true) || $place->has_bedding,
            'has_towel' => in_array('towel', $promisedTypes, true) || $place->has_towel,
            'has_locker' => in_array('locker', $promisedTypes, true) || $place->has_locker,
        ])->save();
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    public function detectMissingPromisedInventory(SleepingPlace $place): Collection
    {
        return InventoryItem::query()
            ->where('sleeping_place_id', $place->id)
            ->where('is_promised_in_listing', true)
            ->whereIn('status', ['missing', 'lost', 'damaged', 'needs_replacement', 'retired', 'disposed'])
            ->orderBy('id')
            ->get();
    }

    public function createMismatchIfPromisedItemMissing(Booking $booking, InventoryIssue $issue): ?object
    {
        if (! $issue->inventoryItem->is_promised_in_listing) {
            return null;
        }

        app(InventoryEventService::class)->recordForIssue($issue, 'listing_mismatch_candidate_created', ['booking_id' => $booking->id]);

        return (object) [
            'booking_id' => $booking->id,
            'inventory_issue_id' => $issue->id,
            'sleeping_place_id' => $issue->sleeping_place_id,
        ];
    }
}
