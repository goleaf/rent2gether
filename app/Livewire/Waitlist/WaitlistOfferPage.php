<?php

namespace App\Livewire\Waitlist;

use App\Models\User;
use App\Models\WaitlistOffer;
use App\Services\Waitlist\WaitlistOfferService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class WaitlistOfferPage extends Component
{
    #[Locked]
    public int $waitlistOfferId;

    public function mount(WaitlistOffer $waitlistOffer): void
    {
        $this->authorizeOffer($waitlistOffer);
        $this->waitlistOfferId = $waitlistOffer->id;
    }

    public function accept(WaitlistOfferService $offers): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $booking = $offers->accept($user, $this->offer);

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

        $offers->decline($user, $this->offer);
        unset($this->offer);
    }

    public function render(): View
    {
        $offer = $this->offer;

        return view('livewire.waitlist.waitlist-offer-page', [
            'offer' => $offer,
            'title' => $this->title($offer),
            'item' => $offer->waitlistItem,
        ]);
    }

    #[Computed]
    public function offer(): WaitlistOffer
    {
        $offer = WaitlistOffer::query()
            ->select([
                'id',
                'waitlist_item_id',
                'user_id',
                'sleeping_place_id',
                'status',
                'offer_expires_at',
                'current_total_price',
                'currency',
            ])
            ->with([
                'sleepingPlace:id,property_id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
                'sleepingPlace.property:id,title',
                'waitlistItem:id,sleeping_place_id,desired_check_in_date,desired_check_out_date,nights_count,max_total_price,currency',
            ])
            ->findOrFail($this->waitlistOfferId);

        $this->authorizeOffer($offer);

        return $offer;
    }

    private function title(WaitlistOffer $offer): ?string
    {
        $place = $offer->sleepingPlace;

        return $place?->translations?->firstWhere('locale', app()->getLocale())?->title
            ?: $place?->translations?->firstWhere('locale', config('localization.fallback_locale', 'en'))?->title
            ?: $place?->display_name
            ?: $place?->place_number;
    }

    private function authorizeOffer(WaitlistOffer $offer): void
    {
        abort_unless((int) $offer->user_id === (int) auth()->id(), 403);
    }
}
