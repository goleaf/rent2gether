<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceBookingDateLock>
 */
class SleepingPlaceBookingDateLockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => null,
            'booking_quote_id' => null,
            'booking_request_id' => null,
            'booking_extension_id' => null,
            'booking_relocation_id' => null,
            'date' => now()->addDays(7)->toDateString(),
            'lock_type' => 'booked',
            'status' => 'active',
            'expires_at' => null,
            'released_at' => null,
        ];
    }

    public function paymentPending(): static
    {
        return $this->state([
            'lock_type' => 'payment_pending',
            'expires_at' => now()->addMinutes(20),
        ]);
    }
}
