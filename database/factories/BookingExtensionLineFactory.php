<?php

namespace Database\Factories;

use App\Models\BookingExtension;
use App\Models\BookingExtensionLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingExtensionLine>
 */
class BookingExtensionLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_extension_id' => BookingExtension::factory(),
            'line_type' => 'extension_night',
            'label_key' => 'booking_extensions.lines.extension_night',
            'date' => now()->addDays(7)->toDateString(),
            'quantity' => 1,
            'unit_amount' => 20,
            'amount' => 20,
            'currency' => 'EUR',
            'is_discount' => false,
            'is_fee' => false,
            'is_deposit' => false,
            'is_refundable' => false,
            'is_payable_now' => true,
            'sort_order' => 10,
        ];
    }
}
