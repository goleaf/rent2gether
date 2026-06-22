<?php

namespace App\Services\Reviews;

use App\Models\Review;
use App\Models\Room;
use App\Models\RoommateExperienceReview;

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
        $query = RoommateExperienceReview::query()->where('room_id', $room->id);

        return [
            'reviews_count' => (int) $query->count(),
            'quiet_roommates_count' => (int) (clone $query)->where('quiet_roommates', true)->count(),
            'clean_roommates_count' => (int) (clone $query)->where('clean_roommates', true)->count(),
            'friendly_roommates_count' => (int) (clone $query)->where('friendly_roommates', true)->count(),
            'average_rating' => round((float) ($query->avg('roommate_experience_rating') ?: 0), 2),
        ];
    }
}
