<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;

class HintVisibilityService
{
    public function shouldShowOnCard(GuestHintData $hint): bool
    {
        return $hint->showOnCard;
    }

    public function shouldShowOnDetail(GuestHintData $hint): bool
    {
        return $hint->showOnDetail;
    }

    public function shouldShowBeforeBooking(GuestHintData $hint): bool
    {
        return $hint->showBeforeBooking;
    }

    public function shouldShowInFavorites(GuestHintData $hint): bool
    {
        return $hint->showInFavorites;
    }

    public function shouldShowInSavedSearch(GuestHintData $hint): bool
    {
        return $hint->showInSavedSearch;
    }
}
