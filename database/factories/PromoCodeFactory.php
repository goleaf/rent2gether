<?php

namespace Database\Factories;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PromoCode>
 */
class PromoCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'name' => fake()->words(2, true),
            'description' => null,
            'discount_type' => PromoCode::TYPE_PROMO_CODE,
            'value_type' => PromoCode::VALUE_PERCENT,
            'value' => 10,
            'currency' => 'EUR',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'usage_limit' => null,
            'usage_limit_per_user' => null,
            'min_booking_amount' => null,
            'min_nights' => null,
            'new_guest_only' => false,
            'sleeping_place_id' => null,
            'property_id' => null,
            'host_user_id' => null,
            'active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);
    }
}
