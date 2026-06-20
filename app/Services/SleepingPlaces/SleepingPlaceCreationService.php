<?php

namespace App\Services\SleepingPlaces;

use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTemplate;
use App\Models\User;
use App\Services\Calendar\SleepingPlaceCalendarBootstrapService;
use App\Services\Domain\DomainOwnershipService;
use Illuminate\Auth\Access\AuthorizationException;

class SleepingPlaceCreationService
{
    public function __construct(
        private readonly SleepingPlaceService $sleepingPlaces,
        private readonly DomainOwnershipService $ownership,
        private readonly SleepingPlaceCalendarBootstrapService $calendarBootstrap,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function create(User $host, Room $room, array $data): SleepingPlace
    {
        return $this->sleepingPlaces->create($host, $room, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(User $host, SleepingPlace $place, array $data): SleepingPlace
    {
        return $this->sleepingPlaces->update($host, $place, $data);
    }

    /**
     * @throws AuthorizationException
     */
    public function copySleepingPlace(User $host, SleepingPlace $place): SleepingPlace
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);

        $copy = $place->replicate(['publication_status', 'status', 'published_at']);
        $copy->title = trim((string) $place->title.' copy');
        $copy->publication_status = 'draft';
        $copy->status = 'draft';
        $copy->published_at = null;
        $copy->save();
        $this->calendarBootstrap->bootstrap($copy);

        foreach (['physicalDetails', 'comfortDetails', 'storageDetails', 'positionDetails'] as $relation) {
            $place->loadMissing($relation);
            $details = $place->{$relation};

            if ($details) {
                $copyDetails = $details->replicate();
                $copyDetails->sleeping_place_id = $copy->id;
                $copyDetails->save();
            }
        }

        return $copy->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function applyTemplate(User $host, Room $room, SleepingPlaceTemplate $template): SleepingPlace
    {
        $this->ownership->ensureHostOwnsRoom($host, $room);

        if ((int) $template->user_id !== (int) $host->id) {
            throw new AuthorizationException(__('domain.errors.not_room_owner'));
        }

        return $this->create($host, $room, $template->template_json ?? []);
    }
}
