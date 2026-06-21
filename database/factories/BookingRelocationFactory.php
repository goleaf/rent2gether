<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Models\BookingStay;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocation>
 */
class BookingRelocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'relocation_number' => sprintf('REL-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'original_booking_id' => Booking::factory(),
            'new_booking_id' => null,
            'booking_stay_id' => BookingStay::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory()->host(),
            'current_property_id' => Property::factory(),
            'current_room_id' => Room::factory(),
            'current_sleeping_place_id' => SleepingPlace::factory(),
            'new_property_id' => null,
            'new_room_id' => null,
            'new_sleeping_place_id' => null,
            'source_type' => null,
            'source_id' => null,
            'requested_by_user_id' => null,
            'requested_by_type' => 'guest',
            'reason' => 'guest_wants_more_comfort',
            'status' => 'requested',
            'relocation_date' => now()->addDays(2)->toDateString(),
            'relocation_time' => null,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(7)->toDateString(),
            'original_check_in_date' => now()->subDays(2)->toDateString(),
            'original_check_out_date' => now()->addDays(7)->toDateString(),
            'old_period_check_in_date' => now()->subDays(2)->toDateString(),
            'old_period_check_out_date' => now()->addDays(2)->toDateString(),
            'new_period_check_in_date' => now()->addDays(2)->toDateString(),
            'new_period_check_out_date' => now()->addDays(7)->toDateString(),
            'old_remaining_value_amount' => 100,
            'new_remaining_value_amount' => 125,
            'price_difference_amount' => 25,
            'additional_payment_amount' => 25,
            'refund_amount' => 0,
            'additional_deposit_amount' => 0,
            'cleaning_fee_difference_amount' => 0,
            'service_fee_difference_amount' => 0,
            'host_payout_difference_amount' => 25,
            'currency' => 'EUR',
            'price_difference_payer' => 'guest',
            'requires_guest_consent' => true,
            'requires_host_consent' => true,
            'guest_consented_at' => null,
            'host_consented_at' => null,
            'requires_payment' => true,
            'payment_status' => 'waiting_payment',
            'booking_payment_id' => null,
            'payment_method' => null,
            'paid_at' => null,
            'payment_deadline_at' => now()->addMinutes(30),
            'requires_refund' => false,
            'refund_status' => null,
            'booking_refund_id' => null,
            'guest_comment' => null,
            'host_comment' => null,
            'support_comment' => null,
            'future_support_status' => null,
            'future_support_decision' => null,
            'hold_dates' => true,
            'hold_expires_at' => now()->addMinutes(30),
            'expires_at' => now()->addDay(),
        ];
    }
}
