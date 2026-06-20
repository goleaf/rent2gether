<?php

namespace App\Data\Favorites;

final readonly class FavoritePriceChangeResult
{
    public function __construct(
        public string $state,
        public bool $changed,
        public float $amount,
        public float $percent,
        public ?float $previousTotal,
        public ?float $currentTotal,
    ) {}

    public function dropped(): bool
    {
        return $this->amount < -0.01;
    }

    public function increased(): bool
    {
        return $this->amount > 0.01;
    }
}
