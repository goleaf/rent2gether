<?php

namespace App\Data\Waitlist;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

readonly class WaitlistContext
{
    public ?CarbonImmutable $expiresAt;

    public function __construct(
        public CarbonInterface|string $desiredCheckIn,
        public CarbonInterface|string $desiredCheckOut,
        public int $guestsCount = 1,
        public ?float $maxPricePerNight = null,
        public ?float $maxTotalPrice = null,
        public ?float $maxDeposit = null,
        public ?string $currency = null,
        public ?string $source = null,
        public bool $flexibleDates = false,
        public ?int $flexibleDays = null,
        public ?int $minNights = null,
        public ?int $maxNights = null,
        public bool $readyToBookImmediately = false,
        public bool $readyToPayImmediately = false,
        public bool $autoSendRequest = false,
        public bool $autoCreateBookingDraft = false,
        public bool $notifyAvailable = true,
        public bool $notifyPriceDrop = true,
        public bool $notifySimilarAvailable = false,
        public bool $notifyOfferExpiring = true,
        public ?string $guestMessage = null,
        CarbonInterface|string|null $expiresAt = null,
    ) {
        $this->expiresAt = $expiresAt instanceof CarbonInterface
            ? CarbonImmutable::instance($expiresAt)
            : ($expiresAt ? CarbonImmutable::parse($expiresAt) : null);
    }

    public function dateRange(): DateRange
    {
        return new DateRange($this->desiredCheckIn, $this->desiredCheckOut);
    }
}
