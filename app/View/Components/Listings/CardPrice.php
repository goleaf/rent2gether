<?php

namespace App\View\Components\Listings;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Illuminate\View\Component;

class CardPrice extends Component
{
    public readonly string $currency;

    public function __construct(public readonly array $card)
    {
        $this->currency = $card['currency'] ?: 'EUR';
    }

    public function money(float|int|string $amount): string
    {
        return Number::currency((float) $amount, $this->currency, app()->getLocale());
    }

    public function render(): View
    {
        return view('components.listings.card-price');
    }
}
