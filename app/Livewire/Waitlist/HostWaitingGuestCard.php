<?php

namespace App\Livewire\Waitlist;

use App\Models\WaitlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostWaitingGuestCard extends Component
{
    #[Locked]
    public int $waitlistItemId;

    public function mount(int $waitlistItemId): void
    {
        $this->waitlistItemId = $waitlistItemId;
    }

    public function render(): View
    {
        $item = WaitlistItem::query()
            ->select([
                'id',
                'user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'desired_check_in_date',
                'desired_check_out_date',
                'ready_to_book_immediately',
                'position',
            ])
            ->with([
                'user:id,name,rating_as_guest,phone_verified,identity_verified',
                'property:id,host_user_id',
                'sleepingPlace:id,property_id',
                'sleepingPlace.property:id,host_user_id',
            ])
            ->findOrFail($this->waitlistItemId);

        Gate::authorize('viewHost', $item);

        return view('livewire.waitlist.host-waiting-guest-card', [
            'item' => $item,
        ]);
    }
}
