<?php

namespace App\Services\Rooms;

use App\Models\Room;

class RoomLayoutService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateLayoutDetails(Room $room, array $data): void
    {
        $room->layoutDetails()->updateOrCreate(
            ['room_id' => $room->id],
            $data,
        );
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getLayoutSummary(Room $room): array
    {
        $room->loadMissing('layoutDetails');
        $details = $room->layoutDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'area' => $details->area ? __('room.values.area_square_meters', ['count' => $details->area]) : null,
            'windows_count' => $details->windows_count === null ? null : (string) $details->windows_count,
            'window_view' => $details->window_view,
            'cardinal_direction' => $details->cardinal_direction ? __('room.directions.'.$details->cardinal_direction) : null,
            'has_balcony' => $this->yesNo($details->has_balcony),
            'has_free_passage_space' => $this->yesNo($details->has_free_passage_space),
            'narrow_passages' => $this->yesNo($details->narrow_passages),
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
