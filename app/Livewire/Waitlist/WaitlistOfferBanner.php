<?php

namespace App\Livewire\Waitlist;

use App\Models\WaitlistOffer;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WaitlistOfferBanner extends Component
{
    public function render(): View
    {
        return view('livewire.waitlist.waitlist-offer-banner', [
            'offer' => WaitlistOffer::query()
                ->select(['id', 'user_id', 'status', 'offer_expires_at'])
                ->where('user_id', auth()->id())
                ->active()
                ->oldest('offer_expires_at')
                ->first(),
        ]);
    }
}
