<?php

namespace App\Data\SavedSearches;

class SavedSearchNotificationData
{
    /**
     * @param  array<string, scalar|null>  $params
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $type,
        public readonly array $params = [],
        public readonly array $data = [],
        public readonly bool $urgent = false,
    ) {}
}
