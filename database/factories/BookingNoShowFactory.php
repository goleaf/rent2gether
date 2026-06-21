<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingNoShow;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShow>
 */
class BookingNoShowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_show_number' => sprintf('NS-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'booking_check_in_id' => BookingCheckIn::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'status' => 'watching',
            'reason_key' => 'guest_did_not_arrive',
            'check_in_date' => now()->toDateString(),
            'planned_check_in_time' => '18:00',
            'check_in_window' => '18:00-22:00',
            'no_show_started_at' => now(),
            'waiting_period_minutes' => 180,
            'waiting_until' => now()->addMinutes(180),
            'guest_not_answering' => false,
            'guest_warned_late_arrival' => false,
            'guest_warned_cancellation' => false,
            'guest_claimed_arrived' => false,
            'host_marked_no_show' => false,
            'refund_or_penalty_status' => 'not_calculated',
            'refund_amount' => 0,
            'penalty_amount' => 0,
            'deposit_refund_amount' => 0,
            'cleaning_fee_refund_amount' => 0,
            'service_fee_refund_amount' => 0,
            'host_payout_amount' => 0,
            'currency' => 'EUR',
            'calendar_release_status' => 'not_released',
            'future_support_review_required' => false,
        ];
    }
}
