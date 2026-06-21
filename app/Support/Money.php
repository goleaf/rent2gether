<?php

namespace App\Support;

class Money
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency = 'EUR',
    ) {}

    public function decimal(): float
    {
        return round($this->amount, 2);
    }
}
