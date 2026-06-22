<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\PlaceReadinessCheck;
use App\Models\SleepingPlace;

class InventoryReadinessIntegrationService
{
    /**
     * @return array{inventory_ready: bool, missing_required: array<int, int>}
     */
    public function checkInventoryReadiness(SleepingPlace $place): array
    {
        $missingRequired = InventoryItem::query()
            ->where('sleeping_place_id', $place->id)
            ->where('is_required_for_readiness', true)
            ->whereIn('status', ['missing', 'lost', 'damaged', 'needs_replacement', 'retired', 'disposed'])
            ->pluck('id')
            ->all();

        return [
            'inventory_ready' => $missingRequired === [],
            'missing_required' => $missingRequired,
        ];
    }

    public function markReadinessBlockedIfRequiredItemsMissing(PlaceReadinessCheck $check): void
    {
        $result = $this->checkInventoryReadiness($check->sleepingPlace);

        if ($result['inventory_ready']) {
            return;
        }

        $check->forceFill([
            'inventory_ready' => false,
            'status' => 'waiting_inventory',
            'blocking_reason_key' => 'required_inventory_missing',
        ])->save();
    }

    public function markInventoryReady(PlaceReadinessCheck $check): void
    {
        $check->forceFill([
            'inventory_ready' => true,
            'blocking_reason_key' => $check->blocking_reason_key === 'required_inventory_missing' ? null : $check->blocking_reason_key,
        ])->save();
    }
}
