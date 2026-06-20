<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use Illuminate\Support\Collection;

class HintPriorityService
{
    /**
     * @param  Collection<int, GuestHintData>  $hints
     * @return Collection<int, GuestHintData>
     */
    public function sortByImportanceAndContext(Collection $hints, string $context): Collection
    {
        $weights = [
            'critical' => 400,
            'high' => 300,
            'medium' => 200,
            'low' => 100,
        ];

        return $hints
            ->sortByDesc(fn (GuestHintData $hint): int => ($weights[$hint->importance] ?? 0) + $hint->priority)
            ->values();
    }

    /**
     * @param  Collection<int, GuestHintData>  $hints
     * @return Collection<int, GuestHintData>
     */
    public function chooseForCard(Collection $hints, int $limit = 3): Collection
    {
        return $this->sortByImportanceAndContext($hints, 'card')->take($limit)->values();
    }

    /**
     * @param  Collection<int, GuestHintData>  $hints
     * @return Collection<int, GuestHintData>
     */
    public function chooseBeforeBooking(Collection $hints): Collection
    {
        return $this->sortByImportanceAndContext(
            $hints->filter(fn (GuestHintData $hint): bool => $hint->showBeforeBooking),
            'before_booking',
        )->values();
    }

    /**
     * @param  Collection<int, GuestHintData>  $hints
     * @return Collection<int, GuestHintData>
     */
    public function preventDuplicateSimilarHints(Collection $hints): Collection
    {
        return $hints
            ->unique(fn (GuestHintData $hint): string => $hint->key)
            ->values();
    }
}
