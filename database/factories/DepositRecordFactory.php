<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\DepositRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DepositRecord>
 */
class DepositRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => 30,
            'currency' => 'EUR',
            'status' => 'held',
            'held_at' => now(),
            'released_at' => null,
            'withheld_amount' => 0,
            'withhold_reason' => null,
        ];
    }
}
