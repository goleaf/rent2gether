<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPriceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPriceLine>
 */
class BookingPriceLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'type' => 'nightly_subtotal',
            'label_key' => 'booking.price_lines.nightly_subtotal',
            'amount' => 25,
            'currency' => 'EUR',
            'is_refundable' => false,
            'metadata_json' => ['nights' => 1],
        ];
    }
}
