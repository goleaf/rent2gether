<?php

namespace App\Services\Listings;

use App\Models\Property;
use App\Models\User;
use App\Services\Privacy\ListingAddressVisibilityService;

class ListingDetailPrivacyService
{
    public function __construct(
        private readonly ListingAddressVisibilityService $addressVisibility,
    ) {}

    public function canShowEntryInstructions(?Property $property, ?User $viewer): bool
    {
        if (! $property instanceof Property) {
            return false;
        }

        return $this->addressVisibility->canSeeCheckInInstructions($property, $viewer);
    }

    public function canShowHostPrivateContact(?Property $property, ?User $viewer): bool
    {
        if (! $property instanceof Property) {
            return false;
        }

        return $this->addressVisibility->canSeeHostPhone($property, $viewer);
    }
}
