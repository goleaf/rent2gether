<?php

namespace Database\Factories;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationPriceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationPriceLine>
 */
class BookingRelocationPriceLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_relocation_id' => BookingRelocation::factory(),
            'line_type' => 'price_difference',
            'label_key' => 'booking_relocations.lines.price_difference',
            'date' => null,
            'quantity' => 1,
            'unit_amount' => 0,
            'amount' => 0,
            'currency' => 'EUR',
            'is_discount' => false,
            'is_fee' => false,
            'is_deposit' => false,
            'is_refundable' => true,
            'is_payable_now' => true,
            'sort_order' => 10,
        ];
    }
}
