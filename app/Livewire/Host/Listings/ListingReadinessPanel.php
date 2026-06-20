<?php

namespace App\Livewire\Host\Listings;

use App\Models\SleepingPlace;
use App\Services\HostListings\Creation\ListingReadinessService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingReadinessPanel extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function render(ListingReadinessService $readiness): View
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);
        $checks = $place instanceof SleepingPlace ? $readiness->checkSleepingPlace($place) : collect();

        return view('livewire.host.listings.listing-readiness-panel', [
            'checks' => $checks->take(5),
        ]);
    }
}
