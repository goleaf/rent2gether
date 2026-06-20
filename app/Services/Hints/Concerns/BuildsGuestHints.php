<?php

namespace App\Services\Hints\Concerns;

use App\Data\Hints\GuestHintData;

trait BuildsGuestHints
{
    /**
     * @param  array<string, mixed>  $params
     */
    private function hint(
        string $key,
        string $category,
        string $type,
        string $importance,
        int $priority,
        array $params = [],
        bool $card = false,
        bool $detail = true,
        bool $beforeBooking = false,
        bool $favorites = false,
        bool $savedSearch = false,
        bool $dismissible = true,
        ?string $source = null,
        ?string $icon = null,
        ?string $tone = null,
    ): GuestHintData {
        return new GuestHintData(
            key: $key,
            category: $category,
            type: $type,
            importance: $importance,
            priority: $priority,
            messageKey: 'guest_hints.messages.'.$key,
            messageParams: $params,
            source: $source,
            icon: $icon,
            tone: $tone,
            showOnCard: $card,
            showOnDetail: $detail,
            showBeforeBooking: $beforeBooking,
            showInFavorites: $favorites,
            showInSavedSearch: $savedSearch,
            dismissible: $dismissible,
        );
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return filled($value) ? (string) $value : null;
    }
}
