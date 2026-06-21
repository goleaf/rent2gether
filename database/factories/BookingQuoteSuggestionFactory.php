<?php

namespace Database\Factories;

use App\Models\BookingQuote;
use App\Models\BookingQuoteSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingQuoteSuggestion>
 */
class BookingQuoteSuggestionFactory extends Factory
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
            'suggestion_type' => 'nearest_dates',
            'sleeping_place_id' => null,
            'room_id' => null,
            'property_id' => null,
            'check_in_date' => now()->addDays(21)->toDateString(),
            'check_out_date' => now()->addDays(24)->toDateString(),
            'nights_count' => 3,
            'price_preview_amount' => 68,
            'currency' => 'EUR',
            'message_key' => 'booking_quotes.suggestions.nearest_dates',
            'sort_order' => 1,
        ];
    }
}
