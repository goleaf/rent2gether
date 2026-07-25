<?php

namespace App\Services\Properties;

use App\Models\Property;

class PropertyConditionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateConditionDetails(Property $property, array $data): void
    {
        $property->conditionDetails()->updateOrCreate(
            ['property_id' => $property->id],
            $data,
        );
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getConditionSummary(Property $property): array
    {
        $property->loadMissing('conditionDetails');
        $details = $property->conditionDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'repair_state' => $this->level($details->repair_state),
            'cleanliness_level' => $this->level($details->cleanliness_level),
            'smell_level' => $this->level($details->smell_level),
            'humidity_level' => $this->level($details->humidity_level),
            'winter_temperature_level' => $this->level($details->winter_temperature_level),
            'summer_temperature_level' => $this->level($details->summer_temperature_level),
            'indoor_noise_level' => $this->level($details->indoor_noise_level),
            'light_level' => $this->level($details->light_level),
            'has_insects' => $this->yesNo($details->has_insects),
            'has_mold' => $this->yesNo($details->has_mold),
            'furniture_condition' => $this->level($details->furniture_condition),
            'plumbing_condition' => $this->level($details->plumbing_condition),
            'kitchen_condition' => $this->level($details->kitchen_condition),
            'bathroom_condition' => $this->level($details->bathroom_condition),
            'floor_condition' => $this->level($details->floor_condition),
            'walls_condition' => $this->level($details->walls_condition),
            'last_cleaned_at' => $details->last_cleaned_at?->translatedFormat('d M Y'),
            'last_repaired_at' => $details->last_repaired_at?->translatedFormat('d M Y'),
            'last_checked_at' => $details->last_checked_at?->translatedFormat('d M Y'),
        ]);
    }

    /**
     * @return list<string>
     */
    public function getGuestWarnings(Property $property): array
    {
        $property->loadMissing('conditionDetails');
        $details = $property->conditionDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->has_insects ? __('property.warnings.insects') : null,
            $details->has_mold ? __('property.warnings.mold') : null,
            $details->has_heating_problems ? __('property.warnings.heating') : null,
            $details->has_hot_water_problems ? __('property.warnings.hot_water') : null,
            $details->has_damp_marks ? __('property.warnings.damp') : null,
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
                'label' => __('property.fields.'.$field),
                'value' => $value,
            ];
        }

        return $rows;
    }

    private function level(?string $level): ?string
    {
        return $level ? __('property.levels.'.$level) : null;
    }

    private function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('property.values.yes') : __('property.values.no');
    }
}
