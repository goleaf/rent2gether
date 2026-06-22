<?php

namespace App\Services\Reviews;

use App\Enums\ReviewType;
use App\Models\BookingNoShow;
use App\Models\ComplaintCase;
use App\Models\RatingEvent;
use App\Models\Review;
use Illuminate\Support\Collection;

class RatingEventService
{
    public function __construct(
        private readonly ReviewNumberService $numbers,
        private readonly RatingImpactService $impact,
    ) {}

    /**
     * @return Collection<int, RatingEvent>
     */
    public function createFromReview(Review $review): Collection
    {
        $targetType = match ($review->type) {
            ReviewType::HostToGuest => 'guest',
            ReviewType::RoommateExperience => 'room',
            default => 'sleeping_place',
        };

        return collect([[
            'metric_key' => 'overall',
            'impact_value' => (float) $review->overall_rating,
        ]])->map(fn (array $event): RatingEvent => RatingEvent::query()->firstOrCreate(
            [
                'source_type' => 'review',
                'source_id' => $review->id,
                'target_type' => $targetType,
                'metric_key' => $event['metric_key'],
            ],
            [
                'rating_event_number' => $this->numbers->generateRatingEventNumber(),
                'event_key' => 'review_submitted',
                'event_type' => 'review',
                'target_user_id' => $review->target_user_id,
                'property_id' => $review->property_id,
                'room_id' => $review->room_id,
                'sleeping_place_id' => $review->sleeping_place_id,
                'booking_id' => $review->booking_id,
                'booking_stay_id' => $review->booking_stay_id,
                'impact_direction' => $event['impact_value'] >= 4 ? 'positive' : ($event['impact_value'] <= 2 ? 'negative' : 'neutral'),
                'impact_value' => $event['impact_value'],
                'weight' => 1,
                'confirmed' => true,
                'frozen' => false,
                'ignored' => false,
            ],
        ))->values();
    }

    public function createConfirmedComplaintEvent(ComplaintCase $case): ?RatingEvent
    {
        if (! $this->impact->canImpactRating('complaint_case', $case->id)) {
            return null;
        }

        return RatingEvent::query()->firstOrCreate(
            [
                'source_type' => 'complaint_case',
                'source_id' => $case->id,
                'event_key' => 'confirmed_complaint',
                'metric_key' => 'problem_resolution',
            ],
            [
                'rating_event_number' => $this->numbers->generateRatingEventNumber(),
                'event_type' => 'complaint',
                'target_type' => $case->against_type === 'guest' ? 'guest' : 'host',
                'target_user_id' => $case->against_user_id ?: $case->host_user_id,
                'property_id' => $case->property_id,
                'room_id' => $case->room_id,
                'sleeping_place_id' => $case->sleeping_place_id,
                'booking_id' => $case->booking_id,
                'booking_stay_id' => $case->booking_stay_id,
                'impact_direction' => 'negative',
                'impact_value' => -1,
                'weight' => 1,
                'confirmed' => true,
                'frozen' => false,
                'ignored' => false,
                'reason_key' => 'confirmed_complaint',
            ],
        );
    }

    public function createResolvedComplaintEvent(ComplaintCase $case): RatingEvent
    {
        return RatingEvent::query()->create([
            'rating_event_number' => $this->numbers->generateRatingEventNumber(),
            'source_type' => 'complaint_case',
            'source_id' => $case->id,
            'event_key' => 'resolved_complaint',
            'event_type' => 'complaint',
            'target_type' => 'host',
            'target_user_id' => $case->host_user_id,
            'property_id' => $case->property_id,
            'room_id' => $case->room_id,
            'sleeping_place_id' => $case->sleeping_place_id,
            'booking_id' => $case->booking_id,
            'booking_stay_id' => $case->booking_stay_id,
            'metric_key' => 'problem_resolution',
            'impact_direction' => 'positive',
            'impact_value' => 1,
            'weight' => 1,
            'confirmed' => true,
        ]);
    }

    public function createConfirmedMismatchEvent($report): RatingEvent
    {
        return $this->genericConfirmedEvent('listing_mismatch_report', $report, 'confirmed_mismatch', 'description_accuracy', 'sleeping_place');
    }

    public function createConfirmedDepositDeductionEvent($deduction): RatingEvent
    {
        return $this->genericConfirmedEvent('booking_deposit_deduction', $deduction, 'confirmed_deposit_deduction', 'care_for_property', 'guest');
    }

    public function createConfirmedNoShowEvent(BookingNoShow $noShow): RatingEvent
    {
        return $this->genericConfirmedEvent('booking_no_show', $noShow, 'confirmed_no_show', 'punctuality', 'guest');
    }

    public function freezeEvent(RatingEvent $event): RatingEvent
    {
        $event->forceFill(['frozen' => true])->save();

        return $event->refresh();
    }

    public function unfreezeEvent(RatingEvent $event): RatingEvent
    {
        $event->forceFill(['frozen' => false])->save();

        return $event->refresh();
    }

    public function ignoreEvent(RatingEvent $event, string $reason): RatingEvent
    {
        $event->forceFill([
            'ignored' => true,
            'reason_key' => $reason,
        ])->save();

        return $event->refresh();
    }

    private function genericConfirmedEvent(string $sourceType, object $source, string $eventKey, string $metricKey, string $targetType): RatingEvent
    {
        return RatingEvent::query()->create([
            'rating_event_number' => $this->numbers->generateRatingEventNumber(),
            'source_type' => $sourceType,
            'source_id' => $source->id,
            'event_key' => $eventKey,
            'event_type' => 'system',
            'target_type' => $targetType,
            'target_user_id' => $source->guest_user_id ?? $source->host_user_id ?? null,
            'property_id' => $source->property_id ?? null,
            'room_id' => $source->room_id ?? null,
            'sleeping_place_id' => $source->sleeping_place_id ?? null,
            'booking_id' => $source->booking_id ?? null,
            'booking_stay_id' => $source->booking_stay_id ?? null,
            'metric_key' => $metricKey,
            'impact_direction' => 'negative',
            'impact_value' => -1,
            'weight' => 1,
            'confirmed' => true,
        ]);
    }
}
