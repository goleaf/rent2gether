<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationInventoryTransfer;
use Illuminate\Support\Collection;

class BookingRelocationInventoryService
{
    /**
     * @return Collection<int, BookingRelocationInventoryTransfer>
     */
    public function prepareInventoryTransfer(BookingRelocation $relocation): Collection
    {
        if ($relocation->inventoryTransfers()->exists()) {
            return $relocation->inventoryTransfers()->orderBy('id')->get();
        }

        return collect([
            $this->createTransfer($relocation, 'return_old_key', 'Old key'),
            $this->createTransfer($relocation, 'issue_new_key', 'New key'),
            $this->createTransfer($relocation, 'return_old_locker', 'Old locker'),
            $this->createTransfer($relocation, 'assign_new_locker', 'New locker'),
            $this->createTransfer($relocation, 'move_bedding', 'Bedding'),
            $this->createTransfer($relocation, 'move_towel', 'Towel'),
        ]);
    }

    public function returnOldPlaceItems(BookingRelocation $relocation): void
    {
        $relocation->inventoryTransfers()
            ->whereIn('transfer_type', ['return_old_key', 'return_old_locker'])
            ->update(['status' => 'completed', 'updated_at' => now()]);
    }

    public function issueNewPlaceItems(BookingRelocation $relocation): void
    {
        $relocation->inventoryTransfers()
            ->whereIn('transfer_type', ['issue_new_key', 'assign_new_locker'])
            ->update(['status' => 'completed', 'updated_at' => now()]);
    }

    public function transferKeysAndLockerIfNeeded(BookingRelocation $relocation): void
    {
        $this->returnOldPlaceItems($relocation);
        $this->issueNewPlaceItems($relocation);
    }

    public function markTransferCompleted(BookingRelocationInventoryTransfer $transfer): BookingRelocationInventoryTransfer
    {
        $transfer->forceFill([
            'status' => 'completed',
        ])->save();

        return $transfer->refresh();
    }

    private function createTransfer(BookingRelocation $relocation, string $type, string $item): BookingRelocationInventoryTransfer
    {
        return BookingRelocationInventoryTransfer::query()->create([
            'booking_relocation_id' => $relocation->id,
            'booking_id' => $relocation->original_booking_id,
            'inventory_item_id' => null,
            'item_name_snapshot' => $item,
            'transfer_type' => $type,
            'status' => 'pending',
            'from_sleeping_place_id' => $relocation->current_sleeping_place_id,
            'to_sleeping_place_id' => $relocation->new_sleeping_place_id,
            'from_room_id' => $relocation->current_room_id,
            'to_room_id' => $relocation->new_room_id,
            'note' => null,
        ]);
    }
}
