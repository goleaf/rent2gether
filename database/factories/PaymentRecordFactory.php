<?php

namespace Database\Factories;

use App\Enums\PaymentRecordStatus;
use App\Models\Booking;
use App\Models\PaymentRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentRecord>
 */
class PaymentRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'payer_user_id' => User::factory(),
            'provider' => 'manual',
            'provider_reference' => $this->faker->uuid(),
            'amount' => 50,
            'currency' => 'EUR',
            'status' => PaymentRecordStatus::Paid->value,
            'paid_at' => now(),
            'metadata_json' => [],
        ];
    }
}
