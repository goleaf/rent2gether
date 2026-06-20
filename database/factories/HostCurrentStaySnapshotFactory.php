<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\HostCurrentStaySnapshot;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCurrentStaySnapshot>
 */
class HostCurrentStaySnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'guest_user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'guest_display_name' => $this->faker->firstName(),
            'guest_avatar_url' => null,
            'room_label' => 'Room '.$this->faker->numberBetween(1, 9),
            'sleeping_place_label' => 'Place '.$this->faker->numberBetween(1, 9),
            'check_in_date' => now()->subDays(3)->toDateString(),
            'check_in_time' => '14:00',
            'check_out_date' => now()->addDays(4)->toDateString(),
            'check_out_time' => '11:00',
            'nights_count' => 7,
            'nights_left' => 4,
            'payment_status' => 'paid',
            'stay_status' => 'living_now',
            'check_in_status' => 'checked_in',
            'payout_status' => null,
            'booking_total_amount' => 140,
            'paid_amount' => 140,
            'remaining_amount' => 0,
            'deposit_amount' => 30,
            'cleaning_fee_amount' => 5,
            'has_special_requests' => false,
            'special_requests_summary' => null,
            'guest_rating_average' => 4.8,
            'roommate_rating_average' => null,
            'has_complaints' => false,
            'open_complaints_count' => 0,
            'needs_extension' => false,
            'extension_requested_at' => null,
            'needs_checkout' => false,
            'checkout_due_today' => false,
            'checkout_overdue' => false,
            'needs_cleaning_after_checkout' => false,
            'needs_inspection' => false,
            'needs_repair' => false,
            'last_host_note' => null,
            'last_activity_at' => now(),
        ];
    }
}
