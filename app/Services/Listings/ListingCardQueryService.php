<?php

namespace App\Services\Listings;

use App\Data\Listings\ListingCardContext;
use App\Models\Favorite;
use App\Models\SavedSearch;
use App\Models\User;
use App\Queries\Listings\VisibleListingCardsQuery;
use Illuminate\Database\Eloquent\Builder;

class ListingCardQueryService
{
    public function __construct(private readonly VisibleListingCardsQuery $visibleListingCards) {}

    public function baseQuery(ListingCardContext $context): Builder
    {
        return $this->visibleListingCards->handle($context);
    }

    public function forSearch(ListingCardContext $context): Builder
    {
        return $this->baseQuery($context);
    }

    public function forFavorites(User $user, ListingCardContext $context): Builder
    {
        $ids = Favorite::query()
            ->where('user_id', $user->id)
            ->whereNotNull('sleeping_place_id')
            ->pluck('sleeping_place_id')
            ->all();

        return $this->baseQuery($context)->whereIn('sleeping_places.id', $ids);
    }

    public function forSavedSearch(SavedSearch $search, ListingCardContext $context): Builder
    {
        return $this->baseQuery($context)
            ->whereIn('sleeping_places.id', $search->results()->select('sleeping_place_id'));
    }

    /**
     * @param  list<int>  $ids
     */
    public function forComparison(array $ids, ListingCardContext $context): Builder
    {
        return $this->baseQuery($context)->whereIn('sleeping_places.id', $ids);
    }

    public function forRecommendations(User $user, ListingCardContext $context): Builder
    {
        return $this->baseQuery($context)
            ->orderByDesc('sleeping_places.instant_booking_enabled')
            ->orderBy('sleeping_places.base_price_per_night')
            ->limit(12);
    }
}
