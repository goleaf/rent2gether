<?php

namespace App\Services;

use App\Models\SleepingPlace;

class PublicationStatusService
{
    public function canPublishSleepingPlace(SleepingPlace $place): bool
    {
        return $this->getMissingRequiredFields($place) === [];
    }

    /**
     * @return list<string>
     */
    public function getMissingRequiredFields(SleepingPlace $place): array
    {
        $missing = [];

        if (blank($place->title ?: $place->display_name)) {
            $missing[] = 'sleeping_places.publication.missing.title';
        }

        if (! $place->room_id) {
            $missing[] = 'sleeping_places.publication.missing.room';
        }

        if (! $place->property_id) {
            $missing[] = 'sleeping_places.publication.missing.property';
        }

        if (! $place->place_type && ! $place->type) {
            $missing[] = 'sleeping_places.publication.missing.place_type';
        }

        if (! $place->base_price && ! $place->base_price_per_night) {
            $missing[] = 'sleeping_places.publication.missing.base_price';
        }

        return $missing;
    }

    /**
     * @return list<string>
     */
    public function getRecommendedImprovements(SleepingPlace $place): array
    {
        return collect([
            blank($place->mattress_type) ? 'sleeping_places.publication.recommended.mattress' : null,
            ! $place->has_locker ? 'sleeping_places.publication.recommended.locker' : null,
            ! $place->has_socket && ! $place->has_power_socket ? 'sleeping_places.publication.recommended.socket' : null,
            ! $place->has_bedding ? 'sleeping_places.publication.recommended.bedding' : null,
        ])->filter()->values()->all();
    }

    public function calculateCompletionScore(SleepingPlace $place): int
    {
        $required = 5 - count($this->getMissingRequiredFields($place));
        $recommended = 4 - count($this->getRecommendedImprovements($place));

        return (int) round((($required * 2) + $recommended) / 14 * 100);
    }
}
