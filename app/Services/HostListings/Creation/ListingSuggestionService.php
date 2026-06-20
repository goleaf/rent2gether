<?php

namespace App\Services\HostListings\Creation;

use App\Models\HostListingSuggestion;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class ListingSuggestionService
{
    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function generateForProperty(Property $property): Collection
    {
        $property->loadMissing('photos');
        $suggestions = new Collection;

        if ($property->photos->isEmpty()) {
            $suggestions->push($this->record($property, null, null, 'add_property_photo', 'warning'));
        }

        return $suggestions;
    }

    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function generateForRoom(Room $room): Collection
    {
        $room->loadMissing('property', 'photos');
        $suggestions = new Collection;

        if ($room->photos->isEmpty()) {
            $suggestions->push($this->record($room->property, $room, null, 'add_room_photo', 'warning'));
        }

        return $suggestions;
    }

    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function generateForSleepingPlace(SleepingPlace $place): Collection
    {
        $place->loadMissing('property', 'room', 'photos', 'storageDetails');
        $suggestions = new Collection;

        if ($place->photos->isEmpty()) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'add_sleeping_place_photo', 'warning', 'add_photo'));
        }

        if (! $place->storageDetails?->has_locker && ! $place->has_locker) {
            $suggestions->push($this->record($place->property, $place->room, $place, 'specify_personal_locker', 'info', 'edit_storage'));
        }

        return $suggestions;
    }

    /**
     * @throws AuthorizationException
     */
    public function dismiss(User $host, HostListingSuggestion $suggestion): HostListingSuggestion
    {
        if ((int) $suggestion->user_id !== (int) $host->id) {
            throw new AuthorizationException(__('domain.errors.not_property_owner'));
        }

        $suggestion->forceFill(['status' => 'dismissed'])->save();

        return $suggestion->refresh();
    }

    public function markCompleted(HostListingSuggestion $suggestion): HostListingSuggestion
    {
        $suggestion->forceFill(['status' => 'completed'])->save();

        return $suggestion->refresh();
    }

    private function record(Property $property, ?Room $room, ?SleepingPlace $place, string $key, string $severity, ?string $action = null): HostListingSuggestion
    {
        return HostListingSuggestion::query()->firstOrCreate(
            [
                'user_id' => $property->host_user_id ?? $property->user_id,
                'property_id' => $property->id,
                'room_id' => $room?->id,
                'sleeping_place_id' => $place?->id,
                'suggestion_key' => $key,
            ],
            [
                'severity' => $severity,
                'message_key' => 'listing_readiness.suggestions.'.$key,
                'action_key' => $action,
                'status' => 'active',
            ],
        );
    }
}
