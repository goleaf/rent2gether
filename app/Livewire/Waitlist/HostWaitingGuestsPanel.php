<?php

namespace App\Livewire\Waitlist;

use App\Models\SleepingPlace;
use App\Services\Waitlist\WaitlistHostViewService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostWaitingGuestsPanel extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(int $sleepingPlaceId): void
    {
        $place = SleepingPlace::query()
            ->select(['id', 'property_id'])
            ->with('property:id,host_user_id')
            ->findOrFail($sleepingPlaceId);

        abort_unless($place->property?->host_user_id === auth()->id(), 403);

        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function render(WaitlistHostViewService $hostView): View
    {
        $place = SleepingPlace::query()
            ->select(['id', 'property_id'])
            ->findOrFail($this->sleepingPlaceId);

        return view('livewire.waitlist.host-waiting-guests-panel', [
            'summary' => $hostView->summaryForPlace($place),
        ]);
    }
}
