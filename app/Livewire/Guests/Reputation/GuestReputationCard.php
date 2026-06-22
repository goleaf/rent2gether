<?php

namespace App\Livewire\Guests\Reputation;

use App\Models\GuestReputationSnapshot;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GuestReputationCard extends Component
{
    public ?int $guestUserId = null;

    #[Computed]
    public function snapshot(): ?GuestReputationSnapshot
    {
        if ($this->guestUserId === null) {
            return null;
        }

        return GuestReputationSnapshot::query()
            ->select(['id', 'guest_user_id', 'overall_rating', 'reviews_count', 'completed_stays_count'])
            ->where('guest_user_id', $this->guestUserId)
            ->first();
    }

    public function render(): View
    {
        return view('livewire.guests.reputation.guest-reputation-card', [
            'snapshot' => $this->snapshot,
        ]);
    }
}
