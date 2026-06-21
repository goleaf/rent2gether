<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShowMedia>
 */
class BookingNoShowMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_no_show_id' => BookingNoShow::factory(),
            'booking_id' => Booking::factory(),
            'uploaded_by_user_id' => User::factory(),
            'media_type' => 'photo',
            'media_role' => 'guest_arrival_evidence',
            'path' => 'no-show/evidence.jpg',
            'thumbnail_path' => null,
            'caption' => null,
            'visibility' => 'guest_and_host',
        ];
    }
}
