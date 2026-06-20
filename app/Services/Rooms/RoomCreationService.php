<?php

namespace App\Services\Rooms;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomTemplate;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;
use Illuminate\Auth\Access\AuthorizationException;

class RoomCreationService
{
    public function __construct(
        private readonly RoomService $rooms,
        private readonly DomainOwnershipService $ownership,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function create(User $host, Property $property, array $data): Room
    {
        return $this->rooms->create($host, $property, $this->normalize($data));
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(User $host, Room $room, array $data): Room
    {
        return $this->rooms->update($host, $room, $this->normalize($data));
    }

    /**
     * @throws AuthorizationException
     */
    public function copyRoom(User $host, Room $room): Room
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);

        $copy = $room->replicate(['publication_status', 'status']);
        $copy->title = trim($room->title.' copy');
        $copy->status = 'draft';
        $copy->publication_status = 'draft';
        $copy->save();

        $room->loadMissing('comfortDetails');

        if ($room->comfortDetails) {
            $details = $room->comfortDetails->replicate();
            $details->room_id = $copy->id;
            $details->save();
        }

        return $copy->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function applyTemplate(User $host, Property $property, RoomTemplate $template): Room
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);

        if ((int) $template->user_id !== (int) $host->id) {
            throw new AuthorizationException(__('domain.errors.not_property_owner'));
        }

        return $this->create($host, $property, $template->template_json ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $typeMap = [
            'private_room' => 'private',
            'shared_room' => 'shared',
            'dorm_room' => 'dormitory',
            'walk_through_room' => 'shared',
            'living_room' => 'shared',
            'other' => 'shared',
        ];
        $genderMap = [
            'any' => 'no_restriction',
            'female_only' => 'female',
            'male_only' => 'male',
            'private' => 'no_restriction',
        ];

        if (isset($data['room_type'])) {
            $data['room_type'] = $typeMap[$data['room_type']] ?? $data['room_type'];
            $data['type'] = $data['room_type'];
        }

        if (isset($data['gender_policy'])) {
            $data['gender_policy'] = $genderMap[$data['gender_policy']] ?? $data['gender_policy'];
            $data['gender_type'] = $data['gender_policy'];
        }

        return $data;
    }
}
