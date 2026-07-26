<?php

namespace App\Services\Reviews;

use App\Models\GuestReputationSnapshot;
use App\Models\HostReputationSnapshot;
use App\Models\Property;
use App\Models\PropertyRatingSnapshot;
use App\Models\Room;
use App\Models\RoomRatingSnapshot;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRatingSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RatingSnapshotService
{
    public function __construct(private readonly RatingAggregateService $aggregates) {}

    public function recalculateHost(User $host): HostReputationSnapshot
    {
        $targetIds = ['target_user_id' => $host->id];
        $this->aggregates->recalculateTarget('host', $targetIds);
        $overall = $this->aggregates->getAggregate('host', $targetIds, 'overall');

        return HostReputationSnapshot::query()->updateOrCreate(
            ['host_user_id' => $host->id],
            [
                'overall_rating' => $overall?->rating_average ?: 0,
                'description_accuracy_rating' => $this->metric('host', $targetIds, 'description_accuracy'),
                'cleanliness_rating' => $this->metric('host', $targetIds, 'cleanliness'),
                'problem_resolution_rating' => $this->metric('host', $targetIds, 'problem_resolution'),
                'hospitality_rating' => $this->metric('host', $targetIds, 'host_communication'),
                'reviews_count' => $overall?->rating_count ?: 0,
                'completed_stays_count' => $this->completedPublishedStaysForTarget($host),
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function recalculateGuest(User $guest): GuestReputationSnapshot
    {
        $targetIds = ['target_user_id' => $guest->id];
        $this->aggregates->recalculateTarget('guest', $targetIds);
        $overall = $this->aggregates->getAggregate('guest', $targetIds, 'overall');
        $recommendationSummary = User::query()
            ->select(['id'])
            ->withCount([
                'targetedReviews as recommended_by_hosts_count' => fn (Builder $query) => $query->published()->where('recommend_guest', true),
                'targetedReviews as not_recommended_by_hosts_count' => fn (Builder $query) => $query->published()->where('recommend_guest', false),
            ])
            ->find($guest->id);

        return GuestReputationSnapshot::query()->updateOrCreate(
            ['guest_user_id' => $guest->id],
            [
                'overall_rating' => $overall?->rating_average ?: 0,
                'rules_respect_rating' => $this->metric('guest', $targetIds, 'rules_respect'),
                'cleanliness_rating' => $this->metric('guest', $targetIds, 'cleanliness_after_stay'),
                'communication_rating' => $this->metric('guest', $targetIds, 'communication'),
                'punctuality_rating' => $this->metric('guest', $targetIds, 'punctuality'),
                'respect_for_roommates_rating' => $this->metric('guest', $targetIds, 'respect_for_roommates'),
                'care_for_property_rating' => $this->metric('guest', $targetIds, 'care_for_property'),
                'payment_reliability_rating' => $this->metric('guest', $targetIds, 'payment_reliability'),
                'reviews_count' => $overall?->rating_count ?: 0,
                'completed_stays_count' => $this->completedPublishedStaysForTarget($guest),
                'recommended_by_hosts_count' => (int) ($recommendationSummary?->recommended_by_hosts_count ?? 0),
                'not_recommended_by_hosts_count' => (int) ($recommendationSummary?->not_recommended_by_hosts_count ?? 0),
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function recalculateSleepingPlace(SleepingPlace $place): SleepingPlaceRatingSnapshot
    {
        $targetIds = [
            'sleeping_place_id' => $place->id,
            'room_id' => $place->room_id,
            'property_id' => $place->property_id,
        ];
        $this->aggregates->recalculateTarget('sleeping_place', $targetIds);
        $overall = $this->aggregates->getAggregate('sleeping_place', $targetIds, 'overall');
        $reviewSummary = SleepingPlace::query()
            ->select(['id'])
            ->withCount([
                'reviews as published_reviews_count' => fn (Builder $query) => $query->where('status', 'published'),
                'reviews as photo_reviews_count' => fn (Builder $query) => $query->whereHas('reviewMedia'),
            ])
            ->withMax(['reviews as last_review_at' => fn (Builder $query) => $query->where('status', 'published')], 'published_at')
            ->find($place->id);

        return SleepingPlaceRatingSnapshot::query()->updateOrCreate(
            ['sleeping_place_id' => $place->id],
            [
                'room_id' => $place->room_id,
                'property_id' => $place->property_id,
                'host_user_id' => $place->property?->host_user_id ?: $place->property?->user_id ?: $place->user_id,
                'overall_rating' => $overall?->rating_average ?: 0,
                'cleanliness_rating' => $this->metric('sleeping_place', $targetIds, 'cleanliness'),
                'safety_rating' => $this->metric('sleeping_place', $targetIds, 'safety'),
                'location_rating' => $this->metric('sleeping_place', $targetIds, 'location'),
                'description_accuracy_rating' => $this->metric('sleeping_place', $targetIds, 'description_accuracy'),
                'sleeping_place_quality_rating' => $this->metric('sleeping_place', $targetIds, 'sleeping_place_quality'),
                'mattress_quality_rating' => $this->metric('sleeping_place', $targetIds, 'mattress_quality'),
                'noise_level_rating' => $this->metric('sleeping_place', $targetIds, 'noise_level'),
                'amenities_rating' => $this->metric('sleeping_place', $targetIds, 'amenities'),
                'internet_rating' => $this->metric('sleeping_place', $targetIds, 'internet'),
                'value_for_money_rating' => $this->metric('sleeping_place', $targetIds, 'value_for_money'),
                'problem_resolution_rating' => $this->metric('sleeping_place', $targetIds, 'problem_resolution'),
                'reviews_count' => $overall?->rating_count ?: 0,
                'published_reviews_count' => (int) ($reviewSummary?->published_reviews_count ?? 0),
                'photo_reviews_count' => (int) ($reviewSummary?->photo_reviews_count ?? 0),
                'last_review_at' => $reviewSummary?->last_review_at,
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function recalculateRoom(Room $room): RoomRatingSnapshot
    {
        $targetIds = ['room_id' => $room->id, 'property_id' => $room->property_id];
        $this->aggregates->recalculateTarget('room', $targetIds);
        $overall = $this->aggregates->getAggregate('room', $targetIds, 'overall');

        return RoomRatingSnapshot::query()->updateOrCreate(
            ['room_id' => $room->id],
            [
                'property_id' => $room->property_id,
                'host_user_id' => $room->property?->host_user_id ?: $room->property?->user_id,
                'overall_rating' => $overall?->rating_average ?: 0,
                'cleanliness_rating' => $this->metric('room', $targetIds, 'cleanliness'),
                'safety_rating' => $this->metric('room', $targetIds, 'safety'),
                'noise_level_rating' => $this->metric('room', $targetIds, 'noise_level'),
                'roommate_experience_rating' => $this->metric('room', $targetIds, 'roommate_communication'),
                'reviews_count' => $overall?->rating_count ?: 0,
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function recalculateProperty(Property $property): PropertyRatingSnapshot
    {
        $targetIds = ['property_id' => $property->id];
        $this->aggregates->recalculateTarget('property', $targetIds);
        $overall = $this->aggregates->getAggregate('property', $targetIds, 'overall');

        return PropertyRatingSnapshot::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'host_user_id' => $property->host_user_id ?: $property->user_id,
                'overall_rating' => $overall?->rating_average ?: 0,
                'cleanliness_rating' => $this->metric('property', $targetIds, 'cleanliness'),
                'safety_rating' => $this->metric('property', $targetIds, 'safety'),
                'location_rating' => $this->metric('property', $targetIds, 'location'),
                'kitchen_rating' => $this->metric('property', $targetIds, 'kitchen'),
                'bathroom_rating' => $this->metric('property', $targetIds, 'bathroom'),
                'internet_rating' => $this->metric('property', $targetIds, 'internet'),
                'amenities_rating' => $this->metric('property', $targetIds, 'amenities'),
                'description_accuracy_rating' => $this->metric('property', $targetIds, 'description_accuracy'),
                'problem_resolution_rating' => $this->metric('property', $targetIds, 'problem_resolution'),
                'reviews_count' => $overall?->rating_count ?: 0,
                'last_recalculated_at' => now(),
            ],
        );
    }

    /**
     * @param  array<string, int|null>  $targetIds
     */
    private function metric(string $targetType, array $targetIds, string $metricKey): float
    {
        return (float) ($this->aggregates->getAggregate($targetType, $targetIds, $metricKey)?->rating_average ?: 0);
    }

    private function completedPublishedStaysForTarget(User $user): int
    {
        return (int) $user->targetedReviews()
            ->published()
            ->distinct('booking_id')
            ->count('booking_id');
    }
}
