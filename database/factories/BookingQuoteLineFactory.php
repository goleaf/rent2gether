<?php

namespace Database\Factories;

use App\Models\BookingQuote;
use App\Models\BookingQuoteLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingQuoteLine>
 */
class BookingQuoteLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_quote_id' => BookingQuote::factory(),
            'line_type' => 'night',
            'label_key' => 'booking_quotes.lines.night',
            'date' => now()->addDays(14)->toDateString(),
            'quantity' => 1,
            'unit_amount' => 20,
            'amount' => 20,
            'currency' => 'EUR',
            'is_discount' => false,
            'is_fee' => false,
            'is_deposit' => false,
            'is_refundable' => false,
            'is_payable_now' => true,
            'sort_order' => 1,
        ];
    }
}
