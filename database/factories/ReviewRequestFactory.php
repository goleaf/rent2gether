<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\Property;
use App\Models\ReviewRequest;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewRequest>
 */
class ReviewRequestFactory extends Factory
{
    private static int $number = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_request_number' => sprintf('REVR-%s-%06d', now()->format('Y'), self::$number++),
            'booking_id' => Booking::factory(),
            'booking_stay_id' => null,
            'booking_check_out_id' => BookingCheckOut::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'request_type' => 'guest_reviews_place',
            'status' => 'created',
            'reviewer_user_id' => User::factory(),
            'reviewer_type' => 'guest',
            'review_subject_type' => 'sleeping_place',
            'review_subject_user_id' => null,
            'due_at' => now()->addDays(14),
            'opened_at' => null,
            'started_at' => null,
            'submitted_at' => null,
            'expired_at' => null,
            'cancelled_at' => null,
            'closed_at' => null,
            'notification_sent_at' => null,
            'reminder_sent_at' => null,
        ];
    }
}
