<?php

namespace App\Services\Properties;

use App\Models\Property;
use App\Models\User;

class PropertyProfileService
{
    public function __construct(
        private readonly PropertyGuestSummaryService $guestSummary,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function buildForGuest(Property $property, array $context = []): array
    {
        $viewer = ($context['viewer'] ?? null) instanceof User ? $context['viewer'] : null;

        return $this->guestSummary->build($property, $viewer);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildForHost(Property $property): array
    {
        $property->loadMissing(['translations', 'locationDetails', 'conditionDetails', 'accessDetails']);

        return [
            'property' => $property,
            'translations' => $property->translations,
            'location' => $property->locationDetails,
            'condition' => $property->conditionDetails,
            'access' => $property->accessDetails,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateMainInfo(User $host, Property $property, array $data): Property
    {
        abort_unless($property->isOwnedBy($host), 403);

        $property->update($data);

        return $property->refresh();
    }

    public function updateCounts(Property $property): Property
    {
        $activeRooms = $property->rooms()->active()->count();
        $activeSleepingPlaces = $property->sleepingPlaces()->active()->count();
        $occupiedSleepingPlaces = $property->sleepingPlaces()->where('status', 'occupied')->count();
        $unavailableSleepingPlaces = $property->sleepingPlaces()->whereNotIn('status', ['active', 'occupied'])->count();

        $property->update([
            'active_rooms_count' => $activeRooms,
            'active_sleeping_places_count' => $activeSleepingPlaces,
            'occupied_sleeping_places_count' => $occupiedSleepingPlaces,
            'unavailable_sleeping_places_count' => $unavailableSleepingPlaces,
            'free_sleeping_places_count' => max(0, $activeSleepingPlaces - $occupiedSleepingPlaces),
        ]);

        return $property->refresh();
    }
}
