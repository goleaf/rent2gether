<?php

namespace App\Livewire\Host\SleepingPlaces\Concerns;

use App\Models\SleepingPlace;
use App\Models\User;

trait HandlesSleepingPlaceStep
{
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
        return SleepingPlace::query()
            ->with('property')
            ->findOrFail($this->sleepingPlaceId);
    }

    protected function markSaved(): void
    {
        $this->saved = true;
        $this->wasSaved = true;
    }
}
