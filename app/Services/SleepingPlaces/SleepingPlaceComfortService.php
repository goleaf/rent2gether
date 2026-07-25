<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;

class SleepingPlaceComfortService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateComfortDetails(SleepingPlace $place, array $data): void
    {
        $place->comfortDetails()->updateOrCreate(['sleeping_place_id' => $place->id], $data);

        $place->update(array_filter([
            'mattress_type' => $data['mattress_type'] ?? null,
            'mattress_firmness' => $data['mattress_firmness'] ?? null,
            'mattress_condition' => $data['mattress_condition'] ?? null,
            'has_mattress' => filled($data['mattress_type'] ?? null) || filled($data['mattress_firmness'] ?? null),
            'has_pillow' => $data['has_pillow'] ?? null,
            'has_blanket' => $data['has_blanket'] ?? null,
            'has_bedding' => $data['has_bedding'] ?? null,
            'has_towel' => $data['has_towel'] ?? null,
        ], fn (mixed $value): bool => $value !== null));
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getComfortSummary(SleepingPlace $place): array
    {
        $place->loadMissing('comfortDetails');
        $details = $place->comfortDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'mattress_type' => $details->mattress_type ? __('sleeping_place.mattress_types.'.$details->mattress_type) : null,
            'mattress_firmness' => $details->mattress_firmness ? __('sleeping_place.levels.'.$details->mattress_firmness) : null,
            'mattress_condition' => $details->mattress_condition ? __('sleeping_place.levels.'.$details->mattress_condition) : null,
            'mattress_newness' => $details->mattress_newness ? __('sleeping_place.levels.'.$details->mattress_newness) : null,
            'mattress_thickness_cm' => $details->mattress_thickness_cm ? __('sleeping_place.values.centimeters', ['count' => $details->mattress_thickness_cm]) : null,
            'has_mattress_protector' => $this->yesNo($details->has_mattress_protector),
            'has_pillow' => $this->yesNo($details->has_pillow),
            'has_blanket' => $this->yesNo($details->has_blanket),
            'has_bedding' => $this->yesNo($details->has_bedding),
            'bedding_included' => $this->yesNo($details->bedding_included),
            'has_towel' => $this->yesNo($details->has_towel),
            'towel_included' => $this->yesNo($details->towel_included),
            'bedding_changed_before_guest' => $this->yesNo($details->bedding_changed_before_guest),
        ]);
    }

    /**
     * @return list<string>
     */
    public function getSleepQualityWarnings(SleepingPlace $place): array
    {
        $place->loadMissing('comfortDetails');
        $details = $place->comfortDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->mattress_has_stains ? __('sleeping_place.warnings.mattress_stains') : null,
            $details->mattress_has_smell ? __('sleeping_place.warnings.mattress_smell') : null,
            $details->mattress_sags ? __('sleeping_place.warnings.mattress_sags') : null,
            $details->has_bedding === false ? __('sleeping_place.warnings.no_bedding') : null,
            $details->has_towel === false ? __('sleeping_place.warnings.no_towel') : null,
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
