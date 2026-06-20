<?php

namespace App\Services\HostListings\Creation;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;
use Illuminate\Auth\Access\AuthorizationException;

class ListingPublicationService
{
    public function __construct(
        private readonly DomainOwnershipService $ownership,
        private readonly ListingReadinessService $readiness,
    ) {}

    public function canPublish(SleepingPlace $place): bool
    {
        return $this->readiness->getMissingRequiredChecks($place)->isEmpty();
    }

    /**
     * @throws AuthorizationException
     */
    public function publish(User $host, SleepingPlace $place): SleepingPlace
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);

        if (! $this->canPublish($place)) {
            throw new AuthorizationException(__('listing_wizard.messages.not_ready_to_publish'));
        }

        $place->loadMissing('property', 'room');
        $place->property->forceFill(['publication_status' => 'published', 'status' => 'active', 'published_at' => now()])->save();
        $place->room->forceFill(['publication_status' => 'published', 'status' => 'active'])->save();
        $place->forceFill(['publication_status' => 'published', 'status' => 'active', 'published_at' => now()])->save();

        return $place->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function hide(User $host, SleepingPlace $place): SleepingPlace
    {
        return $this->setStatus($host, $place, 'hidden');
    }

    /**
     * @throws AuthorizationException
     */
    public function pause(User $host, SleepingPlace $place): SleepingPlace
    {
        return $this->setStatus($host, $place, 'paused');
    }

    /**
     * @throws AuthorizationException
     */
    public function archive(User $host, SleepingPlace $place): SleepingPlace
    {
        return $this->setStatus($host, $place, 'archived');
    }

    private function setStatus(User $host, SleepingPlace $place, string $status): SleepingPlace
    {
        $this->ownership->ensureHostOwnsSleepingPlace($host, $place);
        $place->forceFill(['publication_status' => $status])->save();

        return $place->refresh();
    }
}
