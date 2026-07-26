<?php

namespace App\Services\Reviews;

use App\Models\Review;
use App\Models\Room;
use App\Models\RoommateExperienceReview;
use Illuminate\Database\Eloquent\Builder;

class RoommateExperienceReviewService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromGuestReview(Review $review, array $data): RoommateExperienceReview
    {
        return RoommateExperienceReview::query()->create([
            'review_id' => $review->id,
            'booking_id' => $review->booking_id,
            'room_id' => (int) $review->room_id,
            'property_id' => (int) $review->property_id,
            'sleeping_place_id' => (int) $review->sleeping_place_id,
            'quiet_roommates' => $data['quiet_roommates'] ?? null,
            'clean_roommates' => $data['clean_roommates'] ?? null,
            'friendly_roommates' => $data['friendly_roommates'] ?? null,
            'roommates_disturbed_sleep' => $data['roommates_disturbed_sleep'] ?? null,
            'roommates_broke_rules' => $data['roommates_broke_rules'] ?? null,
            'conflict_happened' => $data['conflict_happened'] ?? null,
            'roommate_experience_rating' => $data['scores']['roommate_communication'] ?? $data['scores']['overall'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);
    }

    public function updateRoommateAggregates(RoommateExperienceReview $review): void
    {
        app(RoomRatingService::class)->refresh($review->room);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPublicRoommateSummary(Room $room): array
    {
        $room = Room::query()
            ->select(['id'])
            ->withCount([
                'roommateExperienceReviews as roommate_reviews_count',
                'roommateExperienceReviews as quiet_roommates_count' => fn (Builder $query) => $query->where('quiet_roommates', true),
                'roommateExperienceReviews as clean_roommates_count' => fn (Builder $query) => $query->where('clean_roommates', true),
                'roommateExperienceReviews as friendly_roommates_count' => fn (Builder $query) => $query->where('friendly_roommates', true),
            ])
            ->withAvg('roommateExperienceReviews as roommate_average_rating', 'roommate_experience_rating')
            ->find($room->id);

        if (! $room instanceof Room) {
            return [
                'reviews_count' => 0,
                'quiet_roommates_count' => 0,
                'clean_roommates_count' => 0,
                'friendly_roommates_count' => 0,
                'average_rating' => 0.0,
            ];
        }

        return [
            'reviews_count' => (int) $room->roommate_reviews_count,
            'quiet_roommates_count' => (int) $room->quiet_roommates_count,
            'clean_roommates_count' => (int) $room->clean_roommates_count,
            'friendly_roommates_count' => (int) $room->friendly_roommates_count,
            'average_rating' => round((float) ($room->roommate_average_rating ?: 0), 2),
        ];
    }
}
