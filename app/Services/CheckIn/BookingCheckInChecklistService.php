<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInChecklistItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingCheckInChecklistService
{
    /**
     * @return Collection<int, BookingCheckInChecklistItem>
     */
    public function createDefaultChecklist(BookingCheckIn $checkIn): Collection
    {
        foreach ($this->defaultItems() as $itemKey => $required) {
            BookingCheckInChecklistItem::query()->firstOrCreate(
                [
                    'booking_check_in_id' => $checkIn->id,
                    'item_key' => $itemKey,
                ],
                [
                    'label_key' => 'check_in.checklist.'.$itemKey,
                    'required' => $required,
                    'status' => 'pending',
                ],
            );
        }

        return $checkIn->checklistItems()->orderBy('id')->get();
    }

    public function markItemCompleted(User $user, BookingCheckIn $checkIn, string $itemKey): BookingCheckInChecklistItem
    {
        $this->ensureParticipant($user, $checkIn);

        $item = $this->item($checkIn, $itemKey);
        $item->forceFill([
            'status' => 'completed',
            'completed_by_user_id' => $user->id,
            'completed_at' => now(),
        ])->save();

        $this->syncCheckInField($checkIn, $itemKey, true);

        return $item->refresh();
    }

    public function markItemIncomplete(User $user, BookingCheckIn $checkIn, string $itemKey): BookingCheckInChecklistItem
    {
        $this->ensureParticipant($user, $checkIn);

        $item = $this->item($checkIn, $itemKey);
        $item->forceFill([
            'status' => 'pending',
            'completed_by_user_id' => null,
            'completed_at' => null,
        ])->save();

        $this->syncCheckInField($checkIn, $itemKey, false);

        return $item->refresh();
    }

    /**
     * @return Collection<int, BookingCheckInChecklistItem>
     */
    public function getMissingRequiredItems(BookingCheckIn $checkIn): Collection
    {
        return $checkIn->checklistItems()
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
            'keys_handed_over' => true,
            'door_code_shared' => false,
            'room_shown' => true,
            'sleeping_place_shown' => true,
            'rules_explained' => true,
            'kitchen_rules_explained' => false,
            'bathroom_rules_explained' => false,
            'quiet_rules_explained' => false,
            'bedding_given' => true,
            'towel_given' => false,
            'locker_given' => false,
            'before_photo_uploaded' => false,
            'guest_confirmed' => true,
            'host_confirmed' => true,
        ];
    }

    private function item(BookingCheckIn $checkIn, string $itemKey): BookingCheckInChecklistItem
    {
        return BookingCheckInChecklistItem::query()->firstOrCreate(
            ['booking_check_in_id' => $checkIn->id, 'item_key' => $itemKey],
            ['label_key' => 'check_in.checklist.'.$itemKey, 'required' => false, 'status' => 'pending'],
        );
    }

    private function ensureParticipant(User $user, BookingCheckIn $checkIn): void
    {
        if (! in_array((int) $user->id, [(int) $checkIn->guest_user_id, (int) $checkIn->host_user_id], true)) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_participant'),
            ]);
        }
    }

    private function syncCheckInField(BookingCheckIn $checkIn, string $itemKey, bool $value): void
    {
        $columns = [
            'keys_handed_over',
            'door_code_shared',
            'room_shown',
            'sleeping_place_shown',
            'rules_explained',
            'kitchen_rules_explained',
            'bathroom_rules_explained',
            'quiet_rules_explained',
            'bedding_given',
            'towel_given',
            'locker_given',
        ];

        if (in_array($itemKey, $columns, true)) {
            $checkIn->forceFill([$itemKey => $value])->save();
        }
    }
}
