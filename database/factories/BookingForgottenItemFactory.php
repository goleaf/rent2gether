<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingForgottenItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingForgottenItem>
 */
class BookingForgottenItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'item_name' => 'Scarf',
            'description' => $this->faker->sentence(),
            'photo_paths_json' => [],
            'storage_location' => 'Host shelf',
            'status' => 'found',
            'guest_notified_at' => null,
            'picked_up_at' => null,
            'disposed_at' => null,
            'keep_until' => now()->addMonth()->toDateString(),
        ];
    }
}
