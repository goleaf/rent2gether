<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Hints\Concerns\BuildsGuestHints;

class HostHintService
{
    use BuildsGuestHints;

    public function hostRespondsFast(User $host): ?GuestHintData
    {
        $host->loadMissing('hostProfile');

        if ((int) ($host->hostProfile?->response_time_minutes ?? 9999) > 60) {
            return null;
        }

        return $this->hint('host_responds_fast', 'host', 'positive', 'medium', 72, card: true, source: 'host');
    }

    public function hostVerified(User $host): ?GuestHintData
    {
        $host->loadMissing('hostProfile');

        if (! $host->identity_verified && $host->hostProfile?->verified_at === null) {
            return null;
        }

        return $this->hint('host_verified', 'host', 'positive', 'medium', 63, card: true, source: 'host');
    }

    public function hostHighRated(User $host): ?GuestHintData
    {
        $host->loadMissing('hostProfile');
        $rating = (float) ($host->hostProfile?->rating_average ?: $host->rating_as_host ?: 0);

        if ($rating < 4.7) {
            return null;
        }

        return $this->hint('host_high_rated', 'host', 'positive', 'medium', 64, card: true, source: 'host');
    }

    public function hostAllowsExtension(User $host, SleepingPlace $place): ?GuestHintData
    {
        if (! $place->can_extend && ! $place->extensions_allowed) {
            return null;
        }

        return $this->hint('host_allows_extension', 'host', 'positive', 'low', 37, source: 'host');
    }

    public function hostSpeaksGuestLanguage(User $guest, User $host): ?GuestHintData
    {
        $host->loadMissing('hostProfile');
        $languages = collect($host->hostProfile?->languages_json ?? [])->map(fn (mixed $language): string => (string) $language);
        $guestLocale = app()->getLocale();

        if (! $languages->contains($guestLocale)) {
            return null;
        }

        return $this->hint('host_speaks_guest_language', 'host', 'positive', 'low', 36, source: 'host');
    }
}
