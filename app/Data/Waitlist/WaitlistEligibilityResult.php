<?php

namespace App\Data\Waitlist;

readonly class WaitlistEligibilityResult
{
    /**
     * @param  list<string>  $reasons
     */
    public function __construct(
        public bool $eligible,
        public array $reasons = [],
        public ?PriceData $priceData = null,
    ) {}
}
