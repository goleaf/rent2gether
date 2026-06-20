<?php

namespace App\Services\HostListings\Creation;

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\Room;
use App\Models\RoomPhoto;
use App\Models\SleepingPlace;
use App\Models\SleepingPlacePhoto;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;
use Illuminate\Auth\Access\AuthorizationException;

class ListingPhotoService
{
    public function __construct(private readonly DomainOwnershipService $ownership) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function addPropertyPhoto(User $host, Property $property, array $data): PropertyPhoto
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);

        return PropertyPhoto::query()->create($this->photoPayload($host, $data) + ['property_id' => $property->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function addRoomPhoto(User $host, Room $room, array $data): RoomPhoto
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);

        return RoomPhoto::query()->create($this->photoPayload($host, $data) + ['room_id' => $room->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function addSleepingPlacePhoto(User $host, SleepingPlace $place, array $data): SleepingPlacePhoto
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);

        return SleepingPlacePhoto::query()->create($this->photoPayload($host, $data) + ['sleeping_place_id' => $place->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function photoPayload(User $host, array $data): array
    {
        $visibility = in_array(($data['visibility'] ?? 'public'), ['public', 'after_booking', 'host_only'], true)
            ? $data['visibility'] ?? 'public'
            : 'host_only';

        return [
            'uploaded_by_user_id' => $host->id,
            'path' => $data['path'] ?? null,
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_primary' => (bool) ($data['is_main'] ?? $data['is_primary'] ?? false),
            'is_main' => (bool) ($data['is_main'] ?? $data['is_primary'] ?? false),
            'status' => $data['status'] ?? 'active',
            'visibility' => $visibility,
        ];
    }
}
