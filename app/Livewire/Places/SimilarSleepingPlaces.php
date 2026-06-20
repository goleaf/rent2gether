<?php

namespace App\Livewire\Places;

use App\Data\Listings\ListingCardContext;
use App\Models\SleepingPlace;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SimilarSleepingPlaces extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function placeholder(): View
    {
        return view('livewire.places.partials.lazy-placeholder', [
            'label' => __('listing.detail.similar.loading'),
        ]);
    }

    public function render(): View
    {
        $current = SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id'])
            ->with(['property:id,city_id'])
            ->findOrFail($this->sleepingPlaceId);
        $context = new ListingCardContext(
            userId: auth()->id() ? (int) auth()->id() : null,
            locale: app()->getLocale(),
            currency: 'EUR',
            source: 'similar',
            filters: ['variant' => 'compact'],
        );

        $places = app(ListingCardQueryService::class)
            ->forSearch($context)
            ->whereKeyNot($current->id)
            ->where('search_properties.city_id', $current->property?->city_id)
            ->orderBy('sleeping_places.base_price_per_night')
            ->limit(3)
            ->get();

        return view('livewire.places.similar-sleeping-places', [
            'places' => app(ListingCardService::class)
                ->buildMany($places, $context)
                ->map(fn ($card): array => $card->toArray())
                ->all(),
        ]);
    }
}
