<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutMedia>
 */
class BookingCheckOutMediaFactory extends Factory
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
            'uploaded_by_user_id' => User::factory(),
            'media_type' => 'photo',
            'media_role' => 'after_checkout_sleeping_place',
            'path' => 'checkout/'.fake()->uuid().'.jpg',
            'thumbnail_path' => null,
            'caption' => null,
            'visibility' => 'guest_and_host',
        ];
    }
}
