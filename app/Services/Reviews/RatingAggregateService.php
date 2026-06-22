<?php

namespace App\Services\Reviews;

use App\Enums\ReviewType;
use App\Models\RatingAggregate;
use App\Models\Review;
use App\Models\ReviewScore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RatingAggregateService
{
    /**
     * @param  array<string, int|null>  $targetIds
     * @return Collection<int, RatingAggregate>
     */
    public function recalculateTarget(string $targetType, array $targetIds): Collection
    {
        return collect($this->metricsForTarget($targetType))
            ->map(fn (string $metricKey): RatingAggregate => $this->recalculateMetric($targetType, $targetIds, $metricKey))
            ->values();
    }

    /**
     * @param  array<string, int|null>  $targetIds
     */
    public function recalculateMetric(string $targetType, array $targetIds, string $metricKey): RatingAggregate
    {
        $reviewQuery = $this->publishedReviewQuery($targetType, $targetIds);

        $scoreQuery = ReviewScore::query()
            ->whereIn('review_id', (clone $reviewQuery)->select('id'))
            ->where('score_key', $metricKey)
            ->where('is_public', true);

        $count = (int) (clone $scoreQuery)->count();
        $sum = (float) (clone $scoreQuery)->sum('score_value');
        $average = $count > 0 ? round($sum / $count, 2) : 0.0;

        return RatingAggregate::query()->updateOrCreate(
            array_merge(['target_type' => $targetType, 'metric_key' => $metricKey], $this->nullableTargetKeys($targetType, $targetIds)),
            [
                'rating_average' => $average,
                'rating_weighted_average' => $average,
                'rating_count' => $count,
                'rating_sum' => $sum,
                'rating_weight_sum' => $count > 0 ? $count : null,
                'last_review_id' => (clone $reviewQuery)->max('id'),
                'last_recalculated_at' => now(),
            ],
        );
    }

    /**
     * @param  array<string, int|null>  $targetIds
     */
    public function getAggregate(string $targetType, array $targetIds, string $metricKey): ?RatingAggregate
    {
        return RatingAggregate::query()
            ->where(array_merge(['target_type' => $targetType, 'metric_key' => $metricKey], $this->nullableTargetKeys($targetType, $targetIds)))
            ->first();
    }

    /**
     * @return list<string>
     */
    private function metricsForTarget(string $targetType): array
    {
        return match ($targetType) {
            'guest' => ['overall', 'rules_respect', 'cleanliness_after_stay', 'communication', 'punctuality', 'respect_for_roommates', 'care_for_property', 'payment_reliability'],
            'host' => ['overall', 'host_communication', 'description_accuracy', 'cleanliness', 'problem_resolution'],
            'room' => ['overall', 'cleanliness', 'safety', 'noise_level', 'roommate_communication'],
            'property' => ['overall', 'cleanliness', 'safety', 'location', 'kitchen', 'bathroom', 'internet', 'amenities', 'description_accuracy', 'problem_resolution'],
            default => ['overall', 'cleanliness', 'safety', 'location', 'description_accuracy', 'sleeping_place_quality', 'mattress_quality', 'noise_level', 'amenities', 'internet', 'value_for_money', 'problem_resolution'],
        };
    }

    /**
     * @param  array<string, int|null>  $targetIds
     * @return array<string, int>
     */
    private function targetColumns(string $targetType, array $targetIds): array
    {
        return match ($targetType) {
            'guest' => ['target_user_id' => (int) $targetIds['target_user_id']],
            'host' => ['target_user_id' => (int) $targetIds['target_user_id']],
            'room' => ['room_id' => (int) $targetIds['room_id']],
            'property' => ['property_id' => (int) $targetIds['property_id']],
            default => ['sleeping_place_id' => (int) $targetIds['sleeping_place_id']],
        };
    }

    /**
     * @param  array<string, int|null>  $targetIds
     * @return array<string, int|null>
     */
    private function nullableTargetKeys(string $targetType, array $targetIds): array
    {
        return [
            'target_user_id' => in_array($targetType, ['guest', 'host'], true) ? ($targetIds['target_user_id'] ?? null) : null,
            'property_id' => $targetIds['property_id'] ?? null,
            'room_id' => $targetIds['room_id'] ?? null,
            'sleeping_place_id' => $targetIds['sleeping_place_id'] ?? null,
        ];
    }

    /**
     * @return list<string>
     */
    private function reviewTypesForTarget(string $targetType): array
    {
        return match ($targetType) {
            'guest' => [ReviewType::HostToGuest->value],
            'host', 'property', 'sleeping_place' => [ReviewType::GuestToPlace->value],
            'room' => [ReviewType::GuestToPlace->value, ReviewType::RoommateExperience->value],
            default => [],
        };
    }

    /**
     * @param  array<string, int|null>  $targetIds
     * @return Builder<Review>
     */
    private function publishedReviewQuery(string $targetType, array $targetIds): Builder
    {
        $query = Review::query()
            ->where('status', 'published')
            ->where('is_public', true);

        foreach ($this->targetColumns($targetType, $targetIds) as $column => $value) {
            $query->where($column, $value);
        }

        $reviewTypes = $this->reviewTypesForTarget($targetType);

        if ($reviewTypes !== []) {
            $query->whereIn('type', $reviewTypes);
        }

        return $query;
    }
}
