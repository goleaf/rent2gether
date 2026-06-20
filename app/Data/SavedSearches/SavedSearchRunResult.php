<?php

namespace App\Data\SavedSearches;

class SavedSearchRunResult
{
    public function __construct(
        public readonly int $matchedCount,
        public readonly int $newMatchesCount,
        public readonly int $priceDropsCount,
        public readonly int $availableAgainCount,
        public readonly string $messageKey = 'saved_searches.messages.checked',
    ) {}
}
