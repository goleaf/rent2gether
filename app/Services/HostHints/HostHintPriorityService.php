<?php

namespace App\Services\HostHints;

use Illuminate\Support\Collection;

class HostHintPriorityService
{
    /**
     * @param  Collection<int, mixed>  $hints
     * @return Collection<int, mixed>
     */
    public function sortByImportanceAndContext(Collection $hints, string $context = 'dashboard'): Collection
    {
        return $this->avoidDuplicateSimilarHints($hints)
            ->sortByDesc(fn (mixed $hint): int => $this->weight($hint, $context))
            ->values();
    }

    /**
     * @param  Collection<int, mixed>  $hints
     * @return Collection<int, mixed>
     */
    public function showCriticalFirstBeforePublish(Collection $hints): Collection
    {
        return $this->sortByImportanceAndContext($hints, 'before_publish');
    }

    /**
     * @param  Collection<int, mixed>  $hints
     * @return Collection<int, mixed>
     */
    public function limitForWizard(Collection $hints, int $limit = 8): Collection
    {
        return $this->sortByImportanceAndContext($hints, 'wizard')->take($limit)->values();
    }

    /**
     * @param  Collection<int, mixed>  $hints
     * @return Collection<int, mixed>
     */
    public function avoidDuplicateSimilarHints(Collection $hints): Collection
    {
        return $hints
            ->unique(fn (mixed $hint): string => (string) data_get($hint, 'hint_key', data_get($hint, 'key', spl_object_id((object) $hint))))
            ->values();
    }

    /**
     * @param  Collection<int, mixed>  $hints
     * @return Collection<string, Collection<int, mixed>>
     */
    public function groupByCategory(Collection $hints): Collection
    {
        return $hints->groupBy(fn (mixed $hint): string => (string) data_get($hint, 'category', 'general'));
    }

    private function weight(mixed $hint, string $context): int
    {
        $importance = (string) data_get($hint, 'importance', 'medium');
        $priority = (int) data_get($hint, 'priority', 0);
        $beforePublish = (bool) data_get($hint, 'show_before_publish', false);

        $importanceWeight = match ($importance) {
            'critical' => 10_000,
            'high' => 1_000,
            'medium' => 100,
            default => 10,
        };

        if ($context === 'before_publish' && $beforePublish) {
            $importanceWeight += 5_000;
        }

        return $importanceWeight + $priority;
    }
}
