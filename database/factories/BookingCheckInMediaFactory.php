<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInMedia>
 */
class BookingCheckInMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'booking_id' => Booking::factory(),
            'uploaded_by_user_id' => User::factory(),
            'media_type' => 'photo',
            'media_role' => 'before_check_in_sleeping_place',
            'path' => 'check-ins/demo.jpg',
            'thumbnail_path' => null,
            'caption' => null,
            'visibility' => 'guest_and_host',
        ];
    }
}
