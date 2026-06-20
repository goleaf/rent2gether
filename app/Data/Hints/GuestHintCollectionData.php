<?php

namespace App\Data\Hints;

final readonly class GuestHintCollectionData
{
    /**
     * @param  list<GuestHintData>  $hints
     */
    public function __construct(public array $hints) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(?string $locale = null): array
    {
        return collect($this->hints)
            ->map(fn (GuestHintData $hint): array => $hint->toArray($locale))
            ->values()
            ->all();
    }
}
