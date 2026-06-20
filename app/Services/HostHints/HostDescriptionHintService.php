<?php

namespace App\Services\HostHints;

use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;

class HostDescriptionHintService
{
    use BuildsHostHints;

    /**
     * @return list<array<string, mixed>>
     */
    public function forSleepingPlace(SleepingPlace $place): array
    {
        $translation = $place->translations()->where('locale', app()->getLocale())->first()
            ?: $place->translations()->where('locale', config('app.fallback_locale', 'en'))->first();
        $hints = [];

        if (! filled($translation?->summary)) {
            $hints[] = $this->hint('add_short_description', 'description', 'suggestion', 'medium', 100, 'edit_description');
        }

        if (! filled($translation?->description)) {
            $hints[] = $this->hint('add_full_description', 'description', 'suggestion', 'medium', 90, 'edit_description');
        }

        if (! filled($translation?->special_conditions)) {
            $hints[] = $this->hint('add_what_to_know_beforehand', 'description', 'suggestion', 'low', 60, 'edit_description');
        }

        return $hints;
    }
}
