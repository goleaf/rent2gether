<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;

class SleepingPlacePhysicalService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePhysicalDetails(SleepingPlace $place, array $data): void
    {
        $place->physicalDetails()->updateOrCreate(['sleeping_place_id' => $place->id], $data);

        $place->update(array_filter([
            'length_cm' => $data['length_cm'] ?? null,
            'width_cm' => $data['width_cm'] ?? null,
            'height_cm' => $data['height_cm'] ?? null,
            'suitable_for_tall_person' => $data['suitable_for_tall_person'] ?? null,
            'suitable_for_elderly' => $data['suitable_for_elderly'] ?? null,
            'suitable_for_limited_mobility' => $data['suitable_for_limited_mobility'] ?? null,
            'is_accessible' => $data['suitable_for_limited_mobility'] ?? null,
        ], fn (mixed $value): bool => $value !== null));
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getPhysicalSummary(SleepingPlace $place): array
    {
        $place->loadMissing('physicalDetails');
        $details = $place->physicalDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'size' => $details->length_cm && $details->width_cm
                ? __('sleeping_place.values.size_cm', ['length' => $details->length_cm, 'width' => $details->width_cm])
                : null,
            'height_cm' => $details->height_cm ? __('sleeping_place.values.centimeters', ['count' => $details->height_cm]) : null,
            'clearance_above_cm' => $details->clearance_above_cm ? __('sleeping_place.values.centimeters', ['count' => $details->clearance_above_cm]) : null,
            'max_weight_kg' => $details->max_weight_kg ? __('sleeping_place.values.kilograms', ['count' => $details->max_weight_kg]) : null,
            'ladder_available' => $this->yesNo($details->ladder_available),
            'ladder_comfort_level' => $this->level($details->ladder_comfort_level),
            'safety_rail_available' => $this->yesNo($details->safety_rail_available),
            'suitable_for_tall_person' => $this->yesNo($details->suitable_for_tall_person),
            'suitable_for_heavy_person' => $this->yesNo($details->suitable_for_heavy_person),
            'suitable_for_elderly' => $this->yesNo($details->suitable_for_elderly),
            'suitable_for_limited_mobility' => $this->yesNo($details->suitable_for_limited_mobility),
            'frame_stability_level' => $this->level($details->frame_stability_level),
            'squeak_level' => $this->level($details->squeak_level),
        ]);
    }

    /**
     * @return list<string>
     */
    public function getPhysicalWarnings(SleepingPlace $place): array
    {
        $place->loadMissing('physicalDetails');
        $details = $place->physicalDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->not_suitable_for_limited_mobility ? __('sleeping_place.warnings.limited_mobility') : null,
            $details->squeak_level === 'high' ? __('sleeping_place.warnings.squeak') : null,
            $details->frame_stability_level === 'low' ? __('sleeping_place.warnings.frame_stability') : null,
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

    private function level(?string $value): ?string
    {
        return $value ? __('sleeping_place.levels.'.$value) : null;
    }
}
