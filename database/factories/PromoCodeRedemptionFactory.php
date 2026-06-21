<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromoCodeRedemption>
 */
class PromoCodeRedemptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promo_code_id' => PromoCode::factory(),
            'user_id' => User::factory(),
            'booking_quote_id' => null,
            'booking_id' => Booking::factory(),
            'discount_amount' => 5,
            'currency' => 'EUR',
            'redeemed_at' => now(),
        ];
    }
}
