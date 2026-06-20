<?php

namespace App\Data\Waitlist;

readonly class PriceData
{
    public function __construct(
        public float $pricePerNight,
        public float $totalPrice,
        public float $deposit,
        public string $currency,
    ) {}
}
