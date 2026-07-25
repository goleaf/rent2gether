<?php

namespace App\Livewire\Host\SleepingPlaces\Concerns;

use App\Models\SleepingPlace;
use App\Models\User;
use Livewire\Attributes\Locked;

trait HandlesSleepingPlaceStep
{
    #[Locked]
    public int $sleepingPlaceId;

    public bool $saved = false;

    public bool $wasSaved = false;

    protected function mountSleepingPlace(SleepingPlace $sleepingPlace): void
    {
        $user = auth()->user();
        $sleepingPlace->loadMissing('property');

        abort_unless($user instanceof User && $sleepingPlace->property?->isOwnedBy($user), 403);

        $this->sleepingPlaceId = $sleepingPlace->id;
    }

    protected function sleepingPlace(): SleepingPlace
    {
        $sleepingPlace = SleepingPlace::query()
            ->with('property:id,host_user_id,user_id')
            ->findOrFail($this->sleepingPlaceId);

        $user = auth()->user();

        abort_unless($user instanceof User && $sleepingPlace->property?->isOwnedBy($user), 403);

        return $sleepingPlace;
    }

    protected function markSaved(): void
    {
        $this->saved = true;
        $this->wasSaved = true;
    }
}
