<?php

namespace App\Services\Domain;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class DomainOwnershipService
{
    /**
     * @throws AuthorizationException
     */
    public function ensureHostOwnsProperty(User $host, Property $property): void
    {
        if (! $property->isOwnedBy($host)) {
            throw new AuthorizationException(__('domain.errors.not_property_owner'));
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureHostOwnsRoom(User $host, Room $room): void
    {
        $room->loadMissing('property:id,user_id,host_user_id');

        if ((int) $room->user_id === (int) $host->id || $room->property?->isOwnedBy($host)) {
            return;
        }

        throw new AuthorizationException(__('domain.errors.not_room_owner'));
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureHostOwnsSleepingPlace(User $host, SleepingPlace $place): void
    {
        $place->loadMissing('property:id,user_id,host_user_id', 'room:id,user_id,property_id');

        if (
            (int) $place->user_id === (int) $host->id
            || (int) $place->room?->user_id === (int) $host->id
            || $place->property?->isOwnedBy($host)
        ) {
            return;
        }

        throw new AuthorizationException(__('domain.errors.not_sleeping_place_owner'));
    }

    public function canViewSleepingPlace(User $user, SleepingPlace $place): bool
    {
        if ($this->canEditSleepingPlace($user, $place)) {
            return true;
        }

        return in_array((string) $place->publication_status, ['published', 'ready_to_publish'], true)
            || in_array((string) ($place->status?->value ?? $place->status), ['active'], true);
    }

    public function canEditSleepingPlace(User $host, SleepingPlace $place): bool
    {
        try {
            $this->ensureHostOwnsSleepingPlace($host, $place);
        } catch (AuthorizationException) {
            return false;
        }

        return true;
    }
}
