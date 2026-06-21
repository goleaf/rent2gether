<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutInventoryCheck;
use App\Support\Money;
use Illuminate\Support\Collection;

class BookingCheckOutInventoryService
{
    /**
     * @return Collection<int, BookingCheckOutInventoryCheck>
     */
    public function createInventoryChecklist(BookingCheckOut $checkOut): Collection
    {
        foreach ($this->defaultItems() as $itemName) {
            BookingCheckOutInventoryCheck::query()->firstOrCreate(
                [
                    'booking_check_out_id' => $checkOut->id,
                    'item_name_snapshot' => $itemName,
                ],
                [
                    'booking_id' => $checkOut->booking_id,
                    'expected_return' => true,
                    'returned' => false,
                    'lost' => false,
                    'damaged' => false,
                    'needs_replacement' => false,
                    'deduction_requested' => false,
                    'currency' => $checkOut->booking?->currency ?: 'EUR',
                ],
            );
        }

        return $checkOut->inventoryChecks()->orderBy('id')->get();
    }

    public function markItemReturned(BookingCheckOutInventoryCheck $check): BookingCheckOutInventoryCheck
    {
        $check->forceFill([
            'returned' => true,
            'lost' => false,
            'damaged' => false,
            'needs_replacement' => false,
            'deduction_requested' => false,
        ])->save();

        return $check->refresh();
    }

    public function markItemLost(BookingCheckOutInventoryCheck $check, ?Money $deduction = null): BookingCheckOutInventoryCheck
    {
        $check->forceFill([
            'returned' => false,
            'lost' => true,
            'damaged' => false,
            'needs_replacement' => true,
            'deduction_requested' => $deduction !== null,
            'deduction_amount' => $deduction?->decimal(),
            'currency' => $deduction?->currency ?? $check->currency,
        ])->save();

        $this->syncCheckOutInventoryFlags($check->checkOut, $deduction !== null);

        return $check->refresh();
    }

    public function markItemDamaged(BookingCheckOutInventoryCheck $check, ?Money $deduction = null): BookingCheckOutInventoryCheck
    {
        $check->forceFill([
            'returned' => true,
            'damaged' => true,
            'needs_replacement' => true,
            'deduction_requested' => $deduction !== null,
            'deduction_amount' => $deduction?->decimal(),
            'currency' => $deduction?->currency ?? $check->currency,
        ])->save();

        $this->syncCheckOutInventoryFlags($check->checkOut, $deduction !== null);

        return $check->refresh();
    }

    /**
     * @return Collection<int, BookingCheckOutInventoryCheck>
     */
    public function getInventoryIssues(BookingCheckOut $checkOut): Collection
    {
        return $checkOut->inventoryChecks()
            ->where(function ($query): void {
                $query->where('lost', true)
                    ->orWhere('damaged', true)
                    ->orWhere('needs_replacement', true);
            })
            ->get();
    }

    private function syncCheckOutInventoryFlags(BookingCheckOut $checkOut, bool $deductionRequested): void
    {
        $checkOut->forceFill([
            'has_inventory_issue' => true,
            'has_lost_items' => true,
            'has_lost_key' => $checkOut->inventoryChecks()->where('item_name_snapshot', 'key')->where('lost', true)->exists(),
            'deposit_review_required' => true,
            'deposit_deduction_requested' => $checkOut->deposit_deduction_requested || $deductionRequested,
            'needs_deposit_deduction' => $checkOut->needs_deposit_deduction || $deductionRequested,
        ])->save();
    }

    /**
     * @return list<string>
     */
    private function defaultItems(): array
    {
        return [
            'key',
            'access_card',
            'locker',
            'bedding',
            'towel',
        ];
    }
}
