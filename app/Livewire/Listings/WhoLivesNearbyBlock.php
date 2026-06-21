<?php

namespace App\Livewire\Listings;

use App\Models\SleepingPlace;
use App\Services\Stays\GuestRoommatesPreviewService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WhoLivesNearbyBlock extends Component
{
    public ?int $sleepingPlaceId = null;

    public function mount(SleepingPlace|int|null $sleepingPlace = null): void
    {
        $this->sleepingPlaceId = $sleepingPlace instanceof SleepingPlace ? $sleepingPlace->id : ($sleepingPlace ? (int) $sleepingPlace : null);
    }

    public function render(): View
    {
        $place = $this->sleepingPlaceId
            ? SleepingPlace::query()
                ->select(['id', 'room_id'])
                ->with('room:id,property_id,user_id')
                ->find($this->sleepingPlaceId)
            : null;

        return view('livewire.listings.current-roommates-summary', [
            'roommates' => $place ? collect(app(GuestRoommatesPreviewService::class)->getWhoLivesNearbySummary($place)['roommates']) : collect(),
        ]);
    }
}
