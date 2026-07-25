<?php

namespace App\Services\Rooms;

use App\Models\Room;

class RoomAccessService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAccessDetails(Room $room, array $data): void
    {
        $room->accessDetails()->updateOrCreate(
            ['room_id' => $room->id],
            $data,
        );
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getAccessStorageSummary(Room $room): array
    {
        $room->loadMissing('accessDetails');
        $details = $room->accessDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'has_door' => $this->yesNo($details->has_door),
            'has_lock' => $details->has_lock ? __('room.values.locked_room') : $this->yesNo($details->has_lock),
            'has_key' => $this->yesNo($details->has_key),
            'privacy_level' => $details->privacy_level ? __('room.levels.'.$details->privacy_level) : null,
            'has_wardrobe' => $this->yesNo($details->has_wardrobe),
            'has_shared_wardrobe' => $this->yesNo($details->has_shared_wardrobe),
            'has_personal_lockers' => $details->has_personal_lockers ? __('room.values.personal_lockers') : $this->yesNo($details->has_personal_lockers),
            'personal_lockers_count' => $details->personal_lockers_count === null ? null : (string) $details->personal_lockers_count,
            'lockers_have_locks' => $this->yesNo($details->lockers_have_locks),
            'has_luggage_space' => $this->yesNo($details->has_luggage_space),
            'has_desk' => $this->yesNo($details->has_desk),
            'has_chairs' => $this->yesNo($details->has_chairs),
            'has_mirror' => $this->yesNo($details->has_mirror),
            'can_store_food' => $this->yesNo($details->can_store_food),
            'food_storage_allowed_type' => $details->food_storage_allowed_type ? __('room.food_storage.'.$details->food_storage_allowed_type) : null,
        ]);
    }

    /**
     * @param  array<string, ?string>  $values
     * @return list<array{label:string,value:string}>
     */
    private function rows(array $values): array
    {
        $rows = [];

        foreach ($values as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $rows[] = [
                'label' => __('room.fields.'.$field),
                'value' => $value,
            ];
        }

        return $rows;
    }

    private function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('room.values.yes') : __('room.values.no');
    }
}
