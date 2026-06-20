<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;

class SleepingPlaceConditionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateConditionDetails(SleepingPlace $place, array $data): void
    {
        $place->conditionDetails()->updateOrCreate(['sleeping_place_id' => $place->id], $data);
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getConditionSummary(SleepingPlace $place): array
    {
        $place->loadMissing('conditionDetails');
        $details = $place->conditionDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'condition_state' => $this->level($details->condition_state),
            'frame_condition' => $this->level($details->frame_condition),
            'mattress_condition' => $this->level($details->mattress_condition),
            'bedding_condition' => $this->level($details->bedding_condition),
            'curtain_condition' => $this->level($details->curtain_condition),
            'lamp_condition' => $this->level($details->lamp_condition),
            'socket_condition' => $this->level($details->socket_condition),
            'locker_condition' => $this->level($details->locker_condition),
            'last_cleaned_at' => $details->last_cleaned_at?->toDateString(),
            'last_bedding_changed_at' => $details->last_bedding_changed_at?->toDateString(),
            'last_checked_at' => $details->last_checked_at?->toDateString(),
        ]);
    }

    /**
     * @return list<string>
     */
    public function getRepairWarnings(SleepingPlace $place): array
    {
        $place->loadMissing('conditionDetails');
        $details = $place->conditionDetails;

        if (! $details) {
            return [];
        }

        return array_values(array_filter([
            $details->has_damage ? __('sleeping_place.warnings.damage') : null,
            $details->has_stains ? __('sleeping_place.warnings.stains') : null,
            $details->has_smell ? __('sleeping_place.warnings.smell') : null,
            $details->squeaks ? __('sleeping_place.warnings.squeaks') : null,
            $details->needs_repair ? __('sleeping_place.warnings.needs_repair') : null,
            $details->needs_mattress_replacement ? __('sleeping_place.warnings.needs_mattress_replacement') : null,
            $details->needs_bedding_replacement ? __('sleeping_place.warnings.needs_bedding_replacement') : null,
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

    private function level(?string $value): ?string
    {
        return $value ? __('sleeping_place.levels.'.$value) : null;
    }
}
