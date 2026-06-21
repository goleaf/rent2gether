<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutInventoryCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutInventoryCheck>
 */
class BookingCheckOutInventoryCheckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'booking_id' => Booking::factory(),
            'inventory_item_id' => null,
            'item_name_snapshot' => fake()->randomElement(['key', 'towel', 'bedding', 'locker']),
            'expected_return' => true,
            'returned' => false,
            'lost' => false,
            'damaged' => false,
            'needs_replacement' => false,
            'deduction_requested' => false,
            'deduction_amount' => null,
            'currency' => 'EUR',
            'note' => null,
        ];
    }
}
