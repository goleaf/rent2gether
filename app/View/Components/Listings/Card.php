<?php

namespace App\View\Components\Listings;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Card extends Component
{
    public readonly string $cardVariant;

    public readonly bool $embedded;

    public readonly bool $isCompact;

    public readonly int $placeId;

    public function __construct(
        public readonly array $card,
        public readonly bool $showActions = true,
        ?string $cardVariant = null,
        bool|string $embedded = false,
    ) {
        $this->cardVariant = $cardVariant ?: ($card['variant'] ?? 'search');
        $this->embedded = filter_var($embedded, FILTER_VALIDATE_BOOLEAN);
        $this->isCompact = in_array($this->cardVariant, ['compact', 'comparison', 'waitlist', 'host-preview'], true);
        $this->placeId = (int) ($card['sleeping_place_id'] ?? $card['id']);
    }

    public function hintColor(array $hint): string
    {
        return match ($hint['type'] ?? 'info') {
            'warning', 'urgent', 'rule' => 'amber',
            'positive', 'discount' => 'emerald',
            default => 'zinc',
        };
    }

    public function rating(float|int|string|null $rating): string
    {
        return number_format((float) $rating, 1);
    }

    public function render(): View
    {
        return view('components.listings.card');
    }
}
