<?php

namespace App\Livewire\Waitlist;

use App\Models\WaitlistItem;
use Illuminate\Contracts\View\View;
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
        return view('livewire.waitlist.host-waiting-guest-card', [
            'item' => WaitlistItem::query()->with('user:id,name,rating_as_guest,phone_verified,identity_verified')->findOrFail($this->waitlistItemId),
        ]);
    }
}
