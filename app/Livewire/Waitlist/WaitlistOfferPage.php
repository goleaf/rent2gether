<?php

namespace App\Livewire\Waitlist;

use App\Models\User;
use App\Models\WaitlistOffer;
use App\Services\Waitlist\WaitlistOfferService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WaitlistOfferPage extends Component
{
    public WaitlistOffer $waitlistOffer;

    public function mount(WaitlistOffer $waitlistOffer): void
    {
        abort_unless($waitlistOffer->user_id === auth()->id(), 403);
        $this->waitlistOffer = $waitlistOffer;
    }

    public function accept(WaitlistOfferService $offers): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $booking = $offers->accept($user, $this->waitlistOffer);

        $this->redirect(route('guest.bookings.payment', [
            'locale' => app()->getLocale(),
            'booking' => $booking,
        ]), navigate: true);
    }

    public function decline(WaitlistOfferService $offers): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $offers->decline($user, $this->waitlistOffer);
        $this->waitlistOffer = $this->waitlistOffer->fresh();
    }

    public function render(): View
    {
        return view('livewire.waitlist.waitlist-offer-page', [
            'offer' => $this->waitlistOffer->loadMissing(['sleepingPlace.translations', 'sleepingPlace.property', 'waitlistItem']),
        ]);
    }
}
