<?php

namespace App\Services\Properties;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

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
        $counted = Property::query()
            ->select(['id'])
            ->withCount([
                'rooms as active_rooms_count' => fn (Builder $query) => $query->active(),
                'sleepingPlaces as active_sleeping_places_count' => fn (Builder $query) => $query->active(),
                'sleepingPlaces as occupied_sleeping_places_count' => fn (Builder $query) => $query->where('status', 'occupied'),
                'sleepingPlaces as unavailable_sleeping_places_count' => fn (Builder $query) => $query->whereNotIn('status', ['active', 'occupied']),
            ])
            ->find($property->id);

        $activeRooms = (int) ($counted?->active_rooms_count ?? 0);
        $activeSleepingPlaces = (int) ($counted?->active_sleeping_places_count ?? 0);
        $occupiedSleepingPlaces = (int) ($counted?->occupied_sleeping_places_count ?? 0);
        $unavailableSleepingPlaces = (int) ($counted?->unavailable_sleeping_places_count ?? 0);

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
