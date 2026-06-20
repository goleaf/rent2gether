<?php

namespace App\Services\HostListings\Wizard;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Property;
use App\Models\Room;

class HostRoomDraftService
{
    public function createRoom(Property $property, array $data): Room
    {
        return $property->rooms()->create($this->payload($data));
    }

    public function updateRoom(Room $room, array $data): Room
    {
        $room->fill($this->payload($data, $room))->save();

        return $room->refresh();
    }

    public function deleteRoom(Room $room): void
    {
        $room->delete();
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
        return [
            'title' => $data['title'] ?? $room?->title ?? __('listing_wizard.defaults.room_title'),
            'room_number' => $data['room_number'] ?? $room?->room_number,
            'type' => $data['type'] ?? $room?->type?->value ?? RoomType::Shared->value,
            'room_type' => $data['room_type'] ?? $data['type'] ?? $room?->room_type?->value ?? RoomType::Shared->value,
            'gender_policy' => $data['gender_policy'] ?? $room?->gender_policy?->value ?? GenderType::Mixed->value,
            'gender_type' => $data['gender_type'] ?? $data['gender_policy'] ?? $room?->gender_type?->value ?? GenderType::Mixed->value,
            'sleeping_places_count' => $data['sleeping_places_count'] ?? $room?->sleeping_places_count ?? 1,
            'beds_count' => $data['sleeping_places_count'] ?? $room?->beds_count ?? 1,
            'capacity' => $data['sleeping_places_count'] ?? $room?->capacity ?? 1,
            'room_rules_text' => $data['room_rules_text'] ?? $room?->room_rules_text,
            'description' => $data['description'] ?? $room?->description,
            'status' => $data['status'] ?? $room?->status?->value ?? RoomStatus::Draft->value,
            'publication_status' => $data['publication_status'] ?? $room?->publication_status ?? 'draft',
        ];
    }
}
