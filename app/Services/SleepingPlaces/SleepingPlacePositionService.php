<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;

class SleepingPlacePositionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePositionDetails(SleepingPlace $place, array $data): void
    {
        $place->positionDetails()->updateOrCreate(['sleeping_place_id' => $place->id], $data);

        $place->update(array_filter([
            'privacy_level' => $data['privacy_level'] ?? null,
            'has_curtain' => $data['has_curtain'] ?? null,
            'has_privacy_curtain' => $data['has_curtain'] ?? null,
            'has_lamp' => $data['has_personal_lamp'] ?? null,
            'has_personal_lamp' => $data['has_personal_lamp'] ?? null,
            'has_power_socket' => $data['has_power_socket'] ?? null,
            'has_socket' => $data['has_power_socket'] ?? null,
            'has_usb' => $data['has_usb_charger'] ?? null,
            'has_shelf' => $data['has_shelf'] ?? null,
            'has_hook' => $data['has_hook'] ?? null,
            'near_door' => $data['near_door'] ?? null,
            'near_window' => $data['near_window'] ?? null,
            'near_radiator' => $data['near_radiator'] ?? null,
            'near_air_conditioner' => $data['near_air_conditioner'] ?? null,
            'near_passage' => $data['near_passage'] ?? null,
            'noise_level' => $data['noise_level_near_place'] ?? null,
        ], fn (mixed $value): bool => $value !== null));
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getPositionSummary(SleepingPlace $place): array
    {
        $place->loadMissing('positionDetails');
        $details = $place->positionDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'privacy_level' => $details->privacy_level ? __('sleeping_place.levels.'.$details->privacy_level) : null,
            'has_curtain' => $this->yesNo($details->has_curtain),
            'has_personal_lamp' => $this->yesNo($details->has_personal_lamp),
            'has_power_socket' => $this->yesNo($details->has_power_socket),
            'power_sockets_count' => $details->power_sockets_count === null ? null : (string) $details->power_sockets_count,
            'has_usb_charger' => $this->yesNo($details->has_usb_charger),
            'has_shelf' => $this->yesNo($details->has_shelf),
            'has_hook' => $this->yesNo($details->has_hook),
            'near_door' => $this->yesNo($details->near_door),
            'near_window' => $this->yesNo($details->near_window),
            'near_radiator' => $this->yesNo($details->near_radiator),
            'near_air_conditioner' => $this->yesNo($details->near_air_conditioner),
            'near_power_socket' => $this->yesNo($details->near_power_socket ?? $details->near_socket),
            'near_passage' => $this->yesNo($details->near_passage),
            'in_room_corner' => $this->yesNo($details->in_room_corner),
            'noise_level_near_place' => $details->noise_level_near_place ? __('sleeping_place.levels.'.$details->noise_level_near_place) : null,
            'light_level_near_place' => $details->light_level_near_place ? __('sleeping_place.levels.'.$details->light_level_near_place) : null,
            'morning_light' => $this->yesNo($details->morning_light),
            'draft_nearby' => $this->yesNo($details->draft_nearby),
        ]);
    }

    /**
     * @return list<string>
     */
    public function getNoiseAndPrivacyWarnings(SleepingPlace $place): array
    {
        $place->loadMissing('positionDetails');
        $details = $place->positionDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->near_door ? __('sleeping_place.warnings.near_door') : null,
            $details->near_passage ? __('sleeping_place.warnings.near_passage') : null,
            $details->morning_light ? __('sleeping_place.warnings.morning_light') : null,
            $details->draft_nearby ? __('sleeping_place.warnings.draft') : null,
            $details->privacy_level === 'low' ? __('sleeping_place.warnings.low_privacy') : null,
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

            $rows[] = ['label' => __('sleeping_place.fields.'.$field), 'value' => $value];
        }

        return $rows;
    }

    private function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('sleeping_place.values.yes') : __('sleeping_place.values.no');
    }
}
