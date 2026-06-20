<?php

namespace App\Services\SleepingPlaces;

use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Calendar\SleepingPlaceCalendarBootstrapService;
use App\Services\Domain\DomainOwnershipService;
use App\Services\Users\UserRoleModeService;
use Illuminate\Auth\Access\AuthorizationException;

class SleepingPlaceService
{
    public function __construct(
        private readonly DomainOwnershipService $ownership,
        private readonly UserRoleModeService $roles,
        private readonly SleepingPlaceCalendarBootstrapService $calendarBootstrap,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function create(User $host, Room $room, array $data): SleepingPlace
    {
        $this->ensureCanHost($host);
        $this->ownership->ensureHostOwnsRoom($host, $room);

        $place = SleepingPlace::query()->create($this->payload($host, $room, $data));
        $this->calendarBootstrap->bootstrap($place);

        return $place->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(User $host, SleepingPlace $place, array $data): SleepingPlace
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);
        $place->update($this->payload($host, $place->room, $data, false));

        return $place->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function publish(User $host, SleepingPlace $place): SleepingPlace
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);
        $place->forceFill([
            'status' => SleepingPlaceStatus::Active->value,
            'publication_status' => 'published',
            'published_at' => now(),
        ])->save();

        return $place->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function hide(User $host, SleepingPlace $place): SleepingPlace
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);
        $place->forceFill([
            'status' => SleepingPlaceStatus::Hidden->value,
            'publication_status' => 'hidden',
        ])->save();

        return $place->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function archive(User $host, SleepingPlace $place): SleepingPlace
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);
        $place->forceFill([
            'status' => SleepingPlaceStatus::Closed->value,
            'publication_status' => 'archived',
        ])->save();

        return $place->refresh();
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
    private function payload(User $host, Room $room, array $data, bool $creating = true): array
    {
        $placeType = $data['place_type'] ?? $data['type'] ?? 'single_bed';
        $legacyType = $this->legacyPlaceType((string) $placeType);
        $basePrice = $data['base_price'] ?? $data['base_price_per_night'] ?? null;
        $maxGuests = $data['max_guests_count'] ?? $data['max_guests'] ?? 1;
        $title = $data['title'] ?? $data['display_name'] ?? null;

        return array_filter([
            'room_id' => $room->id,
            'property_id' => $room->property_id,
            'user_id' => $host->id,
            'title' => $title,
            'display_name' => $title,
            'place_number' => $data['place_number'] ?? null,
            'place_type' => $placeType,
            'type' => $legacyType,
            'sleeping_place_type' => $legacyType,
            'bed_type' => $data['bed_type'] ?? null,
            'is_top_bunk' => (bool) ($data['is_top_bunk'] ?? $placeType === 'top_bunk'),
            'is_bottom_bunk' => (bool) ($data['is_bottom_bunk'] ?? $placeType === 'bottom_bunk'),
            'is_double_place' => (bool) ($data['is_double_place'] ?? $placeType === 'double_bed'),
            'is_double' => (bool) ($data['is_double_place'] ?? $placeType === 'double_bed'),
            'max_guests_count' => $maxGuests,
            'max_guests' => $maxGuests,
            'base_price' => $basePrice,
            'base_price_per_night' => $basePrice,
            'currency' => $data['currency'] ?? 'EUR',
            'status' => $data['status'] ?? SleepingPlaceStatus::Draft->value,
            'publication_status' => $data['publication_status'] ?? 'draft',
            'has_mattress' => (bool) ($data['has_mattress'] ?? false),
            'mattress_type' => $data['mattress_type'] ?? null,
            'mattress_firmness' => $data['mattress_firmness'] ?? null,
            'mattress_condition' => $data['mattress_condition'] ?? null,
            'has_bedding' => (bool) ($data['has_bedding'] ?? false),
            'has_pillow' => (bool) ($data['has_pillow'] ?? false),
            'has_blanket' => (bool) ($data['has_blanket'] ?? false),
            'has_towel' => (bool) ($data['has_towel'] ?? false),
            'has_privacy_curtain' => (bool) ($data['has_privacy_curtain'] ?? false),
            'has_curtain' => (bool) ($data['has_privacy_curtain'] ?? $data['has_curtain'] ?? false),
            'has_personal_lamp' => (bool) ($data['has_personal_lamp'] ?? false),
            'has_lamp' => (bool) ($data['has_personal_lamp'] ?? $data['has_lamp'] ?? false),
            'has_socket' => (bool) ($data['has_socket'] ?? false),
            'has_power_socket' => (bool) ($data['has_socket'] ?? $data['has_power_socket'] ?? false),
            'has_usb' => (bool) ($data['has_usb'] ?? false),
            'has_shelf' => (bool) ($data['has_shelf'] ?? false),
            'has_hook' => (bool) ($data['has_hook'] ?? false),
            'has_luggage_space' => (bool) ($data['has_luggage_space'] ?? false),
            'has_locker' => (bool) ($data['has_locker'] ?? false),
            'has_lockable_locker' => (bool) ($data['has_lockable_locker'] ?? false),
            'locker_has_lock' => (bool) ($data['has_lockable_locker'] ?? $data['locker_has_lock'] ?? false),
            'suitable_for_tall_guest' => (bool) ($data['suitable_for_tall_guest'] ?? false),
            'suitable_for_tall_person' => (bool) ($data['suitable_for_tall_guest'] ?? $data['suitable_for_tall_person'] ?? false),
            'suitable_for_heavy_guest' => (bool) ($data['suitable_for_heavy_guest'] ?? false),
            'suitable_for_couple' => (bool) ($data['suitable_for_couple'] ?? false),
            'is_for_couple' => (bool) ($data['suitable_for_couple'] ?? $data['is_for_couple'] ?? false),
            'privacy_level' => $data['privacy_level'] ?? null,
            'noise_level' => $data['noise_level'] ?? null,
            'near_door' => (bool) ($data['near_door'] ?? false),
            'near_window' => (bool) ($data['near_window'] ?? false),
            'near_radiator' => (bool) ($data['near_radiator'] ?? false),
            'near_air_conditioner' => (bool) ($data['near_air_conditioner'] ?? false),
            'near_passage' => (bool) ($data['near_passage'] ?? false),
        ], fn (mixed $value): bool => $value !== null);
    }

    private function legacyPlaceType(string $type): string
    {
        return match ($type) {
            'single_bed' => SleepingPlaceType::Single->value,
            'double_bed' => SleepingPlaceType::Double->value,
            'top_bunk' => SleepingPlaceType::BunkTop->value,
            'bottom_bunk' => SleepingPlaceType::BunkBottom->value,
            'folding_bed' => SleepingPlaceType::FoldOut->value,
            default => SleepingPlaceType::tryFrom($type)?->value ?? SleepingPlaceType::Other->value,
        };
    }
}
