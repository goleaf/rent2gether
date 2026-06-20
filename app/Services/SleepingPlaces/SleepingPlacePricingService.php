<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;

class SleepingPlacePricingService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePricing(SleepingPlace $place, array $data): SleepingPlace
    {
        $place->update($data);

        return $place->fresh();
    }
}
