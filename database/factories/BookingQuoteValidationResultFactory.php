<?php

namespace Database\Factories;

use App\Models\BookingQuote;
use App\Models\BookingQuoteValidationResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingQuoteValidationResult>
 */
class BookingQuoteValidationResultFactory extends Factory
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
            'validation_key' => 'sleeping_place_unavailable',
            'severity' => 'blocking',
            'message_key' => 'booking_dates.validation.sleeping_place_unavailable',
            'message_params_json' => [],
            'blocking' => true,
            'visible_to_guest' => true,
            'visible_to_host' => false,
        ];
    }
}
