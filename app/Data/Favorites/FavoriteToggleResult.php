<?php

namespace App\Data\Favorites;

use App\Models\Favorite;

final readonly class FavoriteToggleResult
{
    public function __construct(
        public bool $selected,
        public ?Favorite $favorite,
        public string $messageKey,
    ) {}
}
