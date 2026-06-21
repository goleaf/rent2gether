<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\PaymentReceipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentReceipt>
 */
class PaymentReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'booking_payment_id' => BookingPayment::factory(),
            'guest_user_id' => User::factory(),
            'receipt_number' => sprintf('RCT-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'status' => 'draft',
            'issued_at' => null,
            'receipt_data_json' => null,
            'file_path' => null,
        ];
    }
}
