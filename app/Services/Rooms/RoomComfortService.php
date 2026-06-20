<?php

namespace App\Services\Rooms;

use App\Models\Room;

class RoomComfortService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateComfortDetails(Room $room, array $data): void
    {
        $room->comfortDetails()->updateOrCreate(
            ['room_id' => $room->id],
            $data,
        );
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getComfortSummary(Room $room): array
    {
        $room->loadMissing('comfortDetails');
        $details = $room->comfortDetails;

        if (! $details) {
            return [];
        }

        $quietHours = $details->quiet_hours_enabled && $details->quiet_hours_start && $details->quiet_hours_end
            ? __('room.values.quiet_hours_range', ['start' => $details->quiet_hours_start, 'end' => $details->quiet_hours_end])
            : $this->yesNo($details->quiet_hours_enabled);

        return $this->rows([
            'has_heating' => $this->yesNo($details->has_heating),
            'has_air_conditioning' => $this->yesNo($details->has_air_conditioning),
            'has_fan' => $this->yesNo($details->has_fan),
            'ventilation_level' => $this->level($details->ventilation_level),
            'can_open_window' => $this->yesNo($details->can_open_window),
            'can_close_window' => $this->yesNo($details->can_close_window),
            'light_level' => $this->level($details->light_level),
            'has_blackout_curtains' => $this->yesNo($details->has_blackout_curtains),
            'can_turn_light_at_night' => $this->yesNo($details->can_turn_light_at_night),
            'can_use_personal_lamp_at_night' => $this->yesNo($details->can_use_personal_lamp_at_night),
            'noise_level' => $this->level($details->noise_level),
            'soundproofing_level' => $this->level($details->soundproofing_level),
            'quiet_hours_enabled' => $quietHours,
        ]);
    }

    /**
     * @return list<string>
     */
    public function getGuestWarnings(Room $room): array
    {
        $room->loadMissing('comfortDetails');
        $details = $room->comfortDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->has_draft ? __('room.warnings.draft') : null,
            $details->has_damp_smell ? __('room.warnings.damp_smell') : null,
            $details->has_tobacco_smell ? __('room.warnings.tobacco_smell') : null,
            $details->can_turn_light_at_night === false ? __('room.warnings.no_main_light_at_night') : null,
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
