<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceComfortDetail;
use App\Models\SleepingPlacePhysicalDetail;
use App\Models\SleepingPlacePositionDetail;
use App\Models\SleepingPlaceStorageDetail;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;
use Illuminate\Auth\Access\AuthorizationException;

class SleepingPlaceDetailsService
{
    public function __construct(private readonly DomainOwnershipService $ownership) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function savePhysical(User $host, SleepingPlace $place, array $data): SleepingPlacePhysicalDetail
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);

        return SleepingPlacePhysicalDetail::query()->updateOrCreate(
            ['sleeping_place_id' => $place->id],
            array_merge($data, ['sleeping_place_id' => $place->id]),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function saveComfort(User $host, SleepingPlace $place, array $data): SleepingPlaceComfortDetail
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);

        return SleepingPlaceComfortDetail::query()->updateOrCreate(
            ['sleeping_place_id' => $place->id],
            array_merge($data, ['sleeping_place_id' => $place->id]),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function saveStorage(User $host, SleepingPlace $place, array $data): SleepingPlaceStorageDetail
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);
        $data['has_personal_locker'] = (bool) ($data['has_locker'] ?? $data['has_personal_locker'] ?? false);
        $data['locker_has_lock'] = (bool) ($data['has_lockable_locker'] ?? $data['locker_has_lock'] ?? false);
        $data['has_luggage_space'] = (bool) ($data['has_luggage_storage'] ?? $data['has_luggage_space'] ?? false);

        return SleepingPlaceStorageDetail::query()->updateOrCreate(
            ['sleeping_place_id' => $place->id],
            array_merge($data, ['sleeping_place_id' => $place->id]),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function savePosition(User $host, SleepingPlace $place, array $data): SleepingPlacePositionDetail
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);
        $data['near_power_socket'] = (bool) ($data['near_socket'] ?? $data['near_power_socket'] ?? false);

        return SleepingPlacePositionDetail::query()->updateOrCreate(
            ['sleeping_place_id' => $place->id],
            array_merge($data, ['sleeping_place_id' => $place->id]),
        );
    }
}
