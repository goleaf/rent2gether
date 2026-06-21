<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCancellation>
 */
class BookingCancellationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cancellation_number' => sprintf('CAN-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'booking_cancellation_preview_id' => BookingCancellationPreview::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'cancelled_by_user_id' => null,
            'cancelled_by_type' => 'guest',
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
            'comment' => null,
            'status' => 'booking_cancelled',
            'check_in_date' => now()->addDays(10)->toDateString(),
            'check_out_date' => now()->addDays(12)->toDateString(),
            'cancelled_at' => now(),
            'hours_before_check_in' => 240,
            'nights_before_check_in' => 10,
            'nights_used' => 0,
            'nights_unused' => 2,
            'policy_snapshot_id' => null,
            'accommodation_amount' => 100,
            'cleaning_fee_amount' => 10,
            'service_fee_amount' => 15,
            'deposit_amount' => 50,
            'tax_amount' => 0,
            'city_fee_amount' => 0,
            'accommodation_refund_amount' => 100,
            'cleaning_fee_refund_amount' => 10,
            'service_fee_refund_amount' => 15,
            'deposit_refund_amount' => 50,
            'tax_refund_amount' => 0,
            'city_fee_refund_amount' => 0,
            'penalty_amount' => 0,
            'host_payout_adjustment_amount' => 0,
            'total_refund_amount' => 175,
            'total_non_refundable_amount' => 0,
            'currency' => 'EUR',
            'refund_status' => 'pending',
            'booking_refund_id' => null,
            'calendar_release_status' => 'released',
            'dates_released_at' => now(),
            'requires_host_response' => false,
            'requires_dispute' => false,
        ];
    }
}
