<?php

namespace App\Livewire\SleepingPlaces;

use App\Data\Listings\ListingCardContext;
use App\Models\SleepingPlace;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SleepingPlacePublicCard extends Component
{
    public ?int $sleepingPlaceId = null;

    public function mount(?int $sleepingPlaceId = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function card(): ?array
    {
        if (! $this->sleepingPlaceId) {
            return null;
        }

        $context = new ListingCardContext(
            userId: auth()->id(),
            locale: app()->getLocale(),
            currency: 'EUR',
            checkInDate: null,
            checkOutDate: null,
            guestsCount: 1,
            source: 'foundation_public_card',
        );

        $place = app(ListingCardQueryService::class)
            ->forComparison([$this->sleepingPlaceId], $context)
            ->first();

        return $place instanceof SleepingPlace
            ? app(ListingCardService::class)->build($place, $context)->toArray()
            : null;
    }

    public function render(): View
    {
        return view('livewire.sleeping-places.sleeping-place-public-card');
    }
}
