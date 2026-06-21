<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStay>
 */
class BookingStayFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-10 days', '-1 day');
        $checkOut = (clone $checkIn)->modify('+'.fake()->numberBetween(3, 30).' days');
        $nights = (int) $checkIn->diff($checkOut)->days;

        return [
            'stay_number' => sprintf('STAY-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'status' => 'active',
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_in_time' => '15:00',
            'actual_check_in_at' => $checkIn,
            'planned_check_out_date' => $checkOut->format('Y-m-d'),
            'planned_check_out_time' => '10:00',
            'actual_check_out_at' => null,
            'nights_count' => $nights,
            'calendar_presence_days_count' => $nights + 1,
            'nights_passed' => min($nights, fake()->numberBetween(0, $nights)),
            'nights_remaining' => fake()->numberBetween(0, $nights),
            'payment_status' => 'paid',
            'deposit_status' => null,
            'cleaning_status' => null,
            'inspection_status' => null,
            'has_open_complaint' => false,
            'has_open_maintenance' => false,
            'has_neighbor_problem' => false,
            'has_payment_problem' => false,
            'has_deposit_issue' => false,
            'extension_requested' => false,
            'relocation_requested' => false,
            'checkout_soon' => false,
            'checkout_required' => false,
            'started_at' => $checkIn,
            'ended_at' => null,
            'closed_at' => null,
        ];
    }
}
