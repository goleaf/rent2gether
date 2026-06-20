<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutChecklistItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingCheckOutChecklistService
{
    /**
     * @return Collection<int, BookingCheckOutChecklistItem>
     */
    public function createDefaultChecklist(BookingCheckOut $checkOut): Collection
    {
        foreach ($this->defaultItems() as $itemKey => $required) {
            BookingCheckOutChecklistItem::query()->firstOrCreate(
                [
                    'booking_check_out_id' => $checkOut->id,
                    'item_key' => $itemKey,
                ],
                [
                    'label_key' => 'check_out.checklist.'.$itemKey,
                    'required' => $required,
                    'status' => 'pending',
                ],
            );
        }

        return $checkOut->checklistItems()->orderBy('id')->get();
    }

    public function markItemCompleted(User $user, BookingCheckOut $checkOut, string $itemKey): BookingCheckOutChecklistItem
    {
        $this->ensureParticipant($user, $checkOut);

        $item = $this->item($checkOut, $itemKey);
        $item->forceFill([
            'status' => 'completed',
            'completed_by_user_id' => $user->id,
            'completed_at' => now(),
        ])->save();

        $this->syncCheckOutField($checkOut, $itemKey, true);

        return $item->refresh();
    }

    public function markItemIncomplete(User $user, BookingCheckOut $checkOut, string $itemKey): BookingCheckOutChecklistItem
    {
        $this->ensureParticipant($user, $checkOut);

        $item = $this->item($checkOut, $itemKey);
        $item->forceFill([
            'status' => 'pending',
            'completed_by_user_id' => null,
            'completed_at' => null,
        ])->save();

        $this->syncCheckOutField($checkOut, $itemKey, false);

        return $item->refresh();
    }

    /**
     * @return Collection<int, BookingCheckOutChecklistItem>
     */
    public function getMissingRequiredItems(BookingCheckOut $checkOut): Collection
    {
        return $checkOut->checklistItems()
            ->where('required', true)
            ->where('status', '!=', 'completed')
            ->get();
    }

    /**
     * @return array<string, bool>
     */
    private function defaultItems(): array
    {
        return [
            'keys_returned' => true,
            'locker_emptied' => true,
            'personal_items_taken' => true,
            'bedding_returned' => false,
            'towel_returned' => false,
            'sleeping_place_free' => true,
            'room_checked' => true,
            'after_photo_uploaded' => false,
            'guest_confirmed' => true,
            'host_confirmed' => true,
            'deposit_reviewed' => false,
            'cleaning_created' => true,
            'review_requested' => false,
        ];
    }

    private function item(BookingCheckOut $checkOut, string $itemKey): BookingCheckOutChecklistItem
    {
        return BookingCheckOutChecklistItem::query()->firstOrCreate(
            ['booking_check_out_id' => $checkOut->id, 'item_key' => $itemKey],
            ['label_key' => 'check_out.checklist.'.$itemKey, 'required' => false, 'status' => 'pending'],
        );
    }

    private function ensureParticipant(User $user, BookingCheckOut $checkOut): void
    {
        if (! in_array((int) $user->id, [(int) $checkOut->guest_user_id, (int) $checkOut->host_user_id], true)) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_participant'),
            ]);
        }
    }

    private function syncCheckOutField(BookingCheckOut $checkOut, string $itemKey, bool $value): void
    {
        $columns = [
            'keys_returned',
            'locker_emptied',
            'personal_items_taken',
            'bedding_returned',
            'towel_returned',
            'sleeping_place_free',
            'room_checked',
        ];

        if (in_array($itemKey, $columns, true)) {
            $checkOut->forceFill([$itemKey => $value])->save();
        }
    }
}
