<?php

namespace App\Services\Properties;

use App\Models\Property;

class PropertyCompletionService
{
    /**
     * @return array{completed:int,total:int,percentage:int,missing:list<string>}
     */
    public function evaluate(Property $property): array
    {
        $property->loadMissing(['translations', 'locationDetails', 'conditionDetails', 'accessDetails', 'mediaItems']);

        $checks = [
            'title' => $property->translations->contains(fn ($translation): bool => filled($translation->title)),
            'description' => $property->translations->contains(fn ($translation): bool => filled($translation->description) || filled($translation->full_description)),
            'country' => filled($property->country_id) || filled($property->country),
            'city' => filled($property->city_id) || filled($property->city),
            'district' => filled($property->district),
            'address' => filled($property->street) || filled($property->address_line_1),
            'area' => filled($property->total_area) || filled($property->living_area),
            'rooms' => filled($property->rooms_count),
            'condition' => filled($property->conditionDetails?->repair_state) || filled($property->conditionDetails?->cleanliness_level),
            'access' => filled($property->accessDetails?->entrance_type) || $property->accessDetails?->self_check_in_available !== null,
            'entry_rules' => $property->accessDetails?->has_intercom !== null || $property->accessDetails?->has_key_safe !== null,
            'check_in_instruction' => $property->translations->contains(fn ($translation): bool => filled($translation->self_check_in_instructions) || filled($translation->check_in_instructions)),
        ];

        $completed = collect($checks)->filter()->count();
        $missing = collect($checks)
            ->reject()
            ->keys()
            ->map(fn (string $key): string => __('property.completion.items.'.$key))
            ->values()
            ->all();

        return [
            'completed' => $completed,
            'total' => count($checks),
            'percentage' => (int) round(($completed / count($checks)) * 100),
            'missing' => $missing,
        ];
    }

    public function percentage(Property $property): int
    {
        return $this->evaluate($property)['percentage'];
    }
}
