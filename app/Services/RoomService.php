<?php

namespace App\Services;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class RoomService
{
    public function __construct(
        private readonly DomainOwnershipService $ownership,
        private readonly UserRoleModeService $roles,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function create(User $host, Property $property, array $data): Room
    {
        $this->ensureCanHost($host);
        $this->ownership->ensureHostOwnsProperty($host, $property);

        return Room::query()->create($this->payload($host, $property, $data));
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(User $host, Room $room, array $data): Room
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);
        $room->update($this->payload($host, $room->property, $data, false));

        return $room->refresh();
    }

    private function ensureCanHost(User $host): void
    {
        if (! $this->roles->canCreateHostObjects($host)) {
            throw new AuthorizationException(__('domain.errors.host_mode_required'));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(User $host, Property $property, array $data, bool $creating = true): array
    {
        $roomType = $data['room_type'] ?? $data['type'] ?? RoomType::Shared->value;
        $genderPolicy = $data['gender_policy'] ?? GenderType::Mixed->value;
        $rulesText = $data['rules_text'] ?? $data['room_rules_text'] ?? null;

        return array_filter([
            'property_id' => $property->id,
            'user_id' => $host->id,
            'title' => $data['title'] ?? null,
            'room_number' => $data['room_number'] ?? null,
            'type' => $roomType,
            'room_type' => $roomType,
            'living_format' => $data['living_format'] ?? null,
            'gender_policy' => $genderPolicy,
            'gender_type' => $genderPolicy,
            'is_private' => (bool) ($data['is_private'] ?? false),
            'is_shared' => (bool) ($data['is_shared'] ?? true),
            'sleeping_places_count' => $data['sleeping_places_count'] ?? 0,
            'occupied_places_count' => $data['occupied_places_count'] ?? 0,
            'free_sleeping_places_count' => $data['free_places_count'] ?? $data['free_sleeping_places_count'] ?? 0,
            'area' => $data['area'] ?? null,
            'area_sqm' => $data['area'] ?? null,
            'windows_count' => $data['windows_count'] ?? null,
            'has_lockable_door' => (bool) ($data['has_lockable_door'] ?? false),
            'has_lock' => (bool) ($data['has_lockable_door'] ?? $data['has_lock'] ?? false),
            'has_room_key' => (bool) ($data['has_room_key'] ?? false),
            'has_wardrobe' => (bool) ($data['has_wardrobe'] ?? false),
            'has_lockers' => (bool) ($data['has_lockers'] ?? false),
            'has_desk' => (bool) ($data['has_desk'] ?? false),
            'has_chair' => (bool) ($data['has_chairs'] ?? $data['has_chair'] ?? false),
            'has_chairs' => (bool) ($data['has_chairs'] ?? $data['has_chair'] ?? false),
            'has_heating' => (bool) ($data['has_heating'] ?? false),
            'has_air_conditioning' => (bool) ($data['has_air_conditioning'] ?? false),
            'has_ac' => (bool) ($data['has_air_conditioning'] ?? $data['has_ac'] ?? false),
            'has_fan' => (bool) ($data['has_fan'] ?? false),
            'has_balcony' => (bool) ($data['has_balcony'] ?? false),
            'noise_level' => $data['noise_level'] ?? null,
            'light_level' => $data['light_level'] ?? null,
            'ventilation_level' => $data['ventilation_level'] ?? null,
            'room_rules_text' => $rulesText,
            'rules_text' => $rulesText,
            'status' => $data['status'] ?? RoomStatus::Draft->value,
            'publication_status' => $data['publication_status'] ?? 'draft',
            'capacity' => $data['max_guests'] ?? $data['capacity'] ?? 1,
        ], fn (mixed $value): bool => $value !== null);
    }
}
