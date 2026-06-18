<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $checkOut = (clone $checkIn)->modify('+'.random_int(1, 14).' days');
        $nights = (int) $checkIn->diff($checkOut)->days;
        $pricePerNight = $this->faker->randomFloat(2, 10, 30);
        $subtotal = $pricePerNight * $nights;
        $serviceFee = round($subtotal * 0.05, 2);

        return [
            'bed_id' => Bed::factory(),
            'guest_id' => User::factory(),
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'guests_count' => 1,
            'nights' => $nights,
            'price_per_night' => $pricePerNight,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'cleaning_fee' => 5.00,
            'deposit' => 30.00,
            'service_fee' => $serviceFee,
            'total' => $subtotal + 5.00 + 30.00 + $serviceFee,
            'currency' => 'EUR',
            'status' => BookingStatus::Confirmed->value,
            'payment_status' => 'paid',
            'cancellation_policy' => CancellationPolicy::Flexible->value,
        ];
    }
}
