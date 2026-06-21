<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCancellationPolicySnapshot;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCancellationPolicySnapshot>
 */
class BookingCancellationPolicySnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'policy_type' => 'flexible',
            'title_snapshot' => 'Flexible cancellation',
            'description_snapshot' => null,
            'rules_snapshot_json' => [],
            'free_cancellation_until' => now()->addDays(3),
            'cancellation_penalty_starts_at' => now()->addDays(3),
            'first_night_non_refundable' => false,
            'cleaning_fee_refundable_before_check_in' => true,
            'service_fee_refundable' => false,
            'deposit_always_refundable_before_check_in' => true,
        ];
    }
}
