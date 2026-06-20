<?php

namespace App\Services\Properties;

use App\Models\Property;

class PropertyLocationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateLocationDetails(Property $property, array $data): void
    {
        $property->locationDetails()->updateOrCreate(
            ['property_id' => $property->id],
            $data,
        );
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getPublicLocationSummary(Property $property): array
    {
        $property->loadMissing('locationDetails');
        $details = $property->locationDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'nearest_metro' => $details->nearest_metro,
            'nearest_bus_stop' => $details->nearest_bus_stop,
            'nearest_shop' => $details->nearest_shop ?: $details->nearest_supermarket,
            'nearest_pharmacy' => $details->nearest_pharmacy,
            'distance_to_center_meters' => $this->distance($details->distance_to_center_meters),
            'transport_minutes_to_center' => $this->minutes($details->transport_minutes_to_center),
            'district_noise_level' => $this->level($details->district_noise_level),
            'district_safety_level' => $this->level($details->district_safety_level),
            'street_lighting_level' => $this->level($details->street_lighting_level),
        ]);
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getTransportSummary(Property $property): array
    {
        $property->loadMissing('locationDetails');
        $details = $property->locationDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'nearest_metro' => $details->nearest_metro,
            'nearest_bus_stop' => $details->nearest_bus_stop,
            'nearest_railway_station' => $details->nearest_railway_station ?: $details->nearest_train_station,
            'nearest_airport' => $details->nearest_airport,
            'transport_convenience_level' => $this->level($details->transport_convenience_level),
            'has_night_transport' => $this->yesNo($details->has_night_transport),
            'easy_to_reach_with_luggage' => $this->yesNo($details->easy_to_reach_with_luggage),
        ]);
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public function getParkingSummary(Property $property): array
    {
        $property->loadMissing('locationDetails');
        $details = $property->locationDetails;

        if (! $details) {
            return [];
        }

        return $this->rows([
            'has_parking_nearby' => $this->yesNo($details->has_parking_nearby),
            'has_free_parking' => $this->yesNo($details->has_free_parking),
            'has_paid_parking' => $this->yesNo($details->has_paid_parking),
            'has_private_parking' => $this->yesNo($details->has_private_parking),
            'has_bicycle_parking' => $this->yesNo($details->has_bicycle_parking),
            'parking_permit_required' => $this->yesNo($details->parking_permit_required),
            'parking_usually_full' => $this->yesNo($details->parking_usually_full),
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
                'label' => __('property.fields.'.$field),
                'value' => $value,
            ];
        }

        return $rows;
    }

    private function distance(?int $meters): ?string
    {
        if ($meters === null) {
            return null;
        }

        return __('property.values.distance_meters', ['count' => $meters]);
    }

    private function minutes(?int $minutes): ?string
    {
        if ($minutes === null) {
            return null;
        }

        return trans_choice('property.values.minutes', $minutes, ['count' => $minutes]);
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
