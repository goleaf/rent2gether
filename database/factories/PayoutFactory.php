<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use App\Models\Booking;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payout>
 */
class PayoutFactory extends Factory
{
    public function definition(): array
    {
        $stayAmount = $this->faker->randomFloat(2, 50, 500);
        $serviceFee = round($stayAmount * 0.05, 2);

        return [
            'host_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'stay_amount' => $stayAmount,
            'service_fee' => $serviceFee,
            'deductions' => 0,
            'compensation' => 0,
            'net_amount' => $stayAmount - $serviceFee,
            'currency' => 'EUR',
            'status' => PayoutStatus::Pending->value,
        ];
    }
}
