<?php

namespace App\Services\HostListings\Wizard;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;

class HostRoomDraftService
{
    public function __construct(private readonly DomainOwnershipService $ownership) {}

    public function createRoom(Property $property, array $data): Room
    {
        return $property->rooms()->create($this->payload($data));
    }

    public function createRoomForHost(User $host, Property $property, array $data): Room
    {
        $this->ownership->ensureHostOwnsProperty($host, $property);

        return $this->createRoom($property, $data);
    }

    public function updateRoom(Room $room, array $data): Room
    {
        $room->fill($this->payload($data, $room))->save();

        return $room->refresh();
    }

    public function updateRoomForHost(User $host, Room $room, array $data): Room
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);

        return $this->updateRoom($room, $data);
    }

    public function deleteRoom(Room $room): void
    {
        $room->delete();
    }

    public function deleteRoomForHost(User $host, Room $room): void
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);

        $this->deleteRoom($room);
    }

    public function syncRooms(Property $property, array $rooms): void
    {
        foreach ($rooms as $roomData) {
            isset($roomData['id'])
                ? $this->updateRoom($property->rooms()->whereKey($roomData['id'])->firstOrFail(), $roomData)
                : $this->createRoom($property, $roomData);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $data, ?Room $room = null): array
    {
        $type = $data['type'] ?? $room?->type?->value ?? RoomType::Shared->value;
        $placesCount = (int) ($data['sleeping_places_count'] ?? $room?->sleeping_places_count ?? 1);
        $livingFormat = $data['living_format'] ?? $room?->living_format ?? 'shared';

        return [
            'title' => $data['title'] ?? $room?->title ?? __('listing_wizard.defaults.room_title'),
            'room_number' => $data['room_number'] ?? $room?->room_number,
            'type' => $type,
            'room_type' => $data['room_type'] ?? $type,
            'living_format' => $livingFormat,
            'is_private' => $type === RoomType::Private->value || $livingFormat === 'private',
            'is_shared' => $type !== RoomType::Private->value || $livingFormat !== 'private',
            'gender_policy' => $data['gender_policy'] ?? $room?->gender_policy?->value ?? GenderType::Mixed->value,
            'gender_type' => $data['gender_type'] ?? $data['gender_policy'] ?? $room?->gender_type?->value ?? GenderType::Mixed->value,
            'sleeping_places_count' => $placesCount,
            'beds_count' => $placesCount,
            'capacity' => $placesCount,
            'max_guests' => $data['max_guests'] ?? $room?->max_guests ?? $placesCount,
            'can_book_individual_places' => $data['can_book_individual_places'] ?? true,
            'can_book_entire_room' => $data['can_book_entire_room'] ?? $type === RoomType::Private->value,
            'rules' => $data['rules'] ?? $room?->rules ?? [],
            'room_rules_text' => $data['room_rules_text'] ?? $room?->room_rules_text,
            'description' => $data['description'] ?? $room?->description,
            'status' => $data['status'] ?? $room?->status?->value ?? RoomStatus::Draft->value,
            'publication_status' => $data['publication_status'] ?? $room?->publication_status ?? 'draft',
        ];
    }
}
