<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;
use App\Models\User;

class SleepingPlaceProfileService
{
    public function __construct(
        private readonly SleepingPlaceGuestSummaryService $guestSummary,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildForGuest(SleepingPlace $place, mixed $context = null): array
    {
        $viewer = $context instanceof User ? $context : auth()->user();

        return $this->guestSummary->build($place, $viewer instanceof User ? $viewer : null);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildForHost(SleepingPlace $place): array
    {
        $place->loadMissing([
            'translations',
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ]);

        return [
            'id' => $place->id,
            'display_name' => $place->display_name,
            'internal_name' => $place->internal_name,
            'status' => $place->status?->value,
            'completion' => app(SleepingPlaceCompletionService::class)->percentage($place),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateMainInfo(User $host, SleepingPlace $place, array $data): SleepingPlace
    {
        $place->loadMissing('property');
        abort_unless($place->property?->isOwnedBy($host), 403);

        $place->update($data);

        return $place->fresh();
    }

    public function updateCounts(SleepingPlace $place): void
    {
        $place->room?->update([
            'sleeping_places_count' => $place->room?->sleepingPlaces()->count() ?? 0,
            'active_sleeping_places_count' => $place->room?->sleepingPlaces()->active()->count() ?? 0,
        ]);
    }
}
