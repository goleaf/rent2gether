<?php

namespace App\Services\Listings;

use App\Data\Listings\ListingCardBadgeData;
use App\Data\Listings\ListingCardOccupancyData;
use App\Data\Listings\ListingCardPriceData;
use App\Models\SleepingPlace;

class ListingCardBadgeService
{
    /**
     * @return list<array{key:string,label:string,tone:string,icon:?string}>
     */
    public function badges(
        SleepingPlace $place,
        ListingCardPriceData $price,
        ListingCardOccupancyData $occupancy,
        ?bool $isAvailable,
        bool $hostVerified,
        bool $selfCheckIn,
    ): array {
        $badges = [];

        if ($isAvailable === true) {
            $badges[] = new ListingCardBadgeData('available', __('listing_card.available'), 'green', 'check-circle');
        } elseif ($isAvailable === false) {
            $badges[] = new ListingCardBadgeData('unavailable', __('listing_card.unavailable'), 'red', 'x-circle');
        }

        if ($place->instant_booking_enabled) {
            $badges[] = new ListingCardBadgeData('instant_booking', __('listing_card.instant_booking'), 'green', 'bolt');
        }

        if ($price->hasDiscount) {
            $badges[] = new ListingCardBadgeData('discount', __('listing_card.discount'), 'emerald', 'tag');
        }

        if ($price->hasFreeCancellation) {
            $badges[] = new ListingCardBadgeData('free_cancellation', __('listing_card.free_cancellation'), 'blue', 'shield-check');
        }

        $badges[] = $price->hasDeposit
            ? new ListingCardBadgeData('deposit', __('listing_card.has_deposit'), 'amber', 'banknotes')
            : new ListingCardBadgeData('no_deposit', __('listing_card.no_deposit'), 'zinc', 'banknotes');

        if ($hostVerified) {
            $badges[] = new ListingCardBadgeData('verified_host', __('listing_card.verified_host'), 'blue', 'shield-check');
        }

        if ($occupancy->roomAvailablePlacesCount === 1) {
            $badges[] = new ListingCardBadgeData('one_place_left', __('listing_card.one_place_left'), 'amber', 'users');
        }

        if ($selfCheckIn) {
            $badges[] = new ListingCardBadgeData('self_check_in', __('listing_card.self_check_in'), 'zinc', 'key');
        }

        if ($place->extensions_allowed) {
            $badges[] = new ListingCardBadgeData('can_extend', __('listing_card.can_extend'), 'zinc', 'calendar-days');
        }

        return collect($badges)
            ->unique('key')
            ->map(fn (ListingCardBadgeData $badge): array => $badge->toArray())
            ->values()
            ->all();
    }
}
