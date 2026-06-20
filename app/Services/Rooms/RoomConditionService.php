<?php

namespace App\Services\Rooms;

use App\Models\Room;

class RoomConditionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateConditionDetails(Room $room, array $data): void
    {
        $room->conditionDetails()->updateOrCreate(
            ['room_id' => $room->id],
            $data,
        );
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getConditionSummary(Room $room): array
    {
        $room->loadMissing('conditionDetails');
        $details = $room->conditionDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'condition_state' => $this->level($details->condition_state),
            'repair_state' => $this->level($details->repair_state),
            'cleanliness_level' => $this->level($details->cleanliness_level),
            'floor_condition' => $this->level($details->floor_condition),
            'walls_condition' => $this->level($details->walls_condition),
            'window_condition' => $this->level($details->window_condition),
            'door_condition' => $this->level($details->door_condition),
            'furniture_condition' => $this->level($details->furniture_condition),
            'has_mold' => $this->yesNo($details->has_mold),
            'has_insects' => $this->yesNo($details->has_insects),
            'last_cleaned_at' => $details->last_cleaned_at?->translatedFormat('d M Y'),
            'last_checked_at' => $details->last_checked_at?->translatedFormat('d M Y'),
        ]);
    }

    /**
     * @return list<string>
     */
    public function getGuestWarnings(Room $room): array
    {
        $room->loadMissing('conditionDetails');
        $details = $room->conditionDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->has_mold ? __('room.warnings.mold') : null,
            $details->has_insects ? __('room.warnings.insects') : null,
            $details->has_bad_smell ? __('room.warnings.bad_smell') : null,
            $details->has_damp_marks ? __('room.warnings.damp_marks') : null,
            $details->needs_repair ? __('room.warnings.needs_repair') : null,
        ]));
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

    private function level(?string $level): ?string
    {
        return $level ? __('room.levels.'.$level) : null;
    }

    private function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('room.values.yes') : __('room.values.no');
    }
}
