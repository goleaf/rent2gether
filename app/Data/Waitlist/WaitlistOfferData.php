<?php

namespace App\Data\Waitlist;

readonly class WaitlistOfferData
{
    public function __construct(
        public int $waitlistItemId,
        public int $userId,
        public int $sleepingPlaceId,
        public string $status,
        public ?string $offerExpiresAt,
    ) {}
}
