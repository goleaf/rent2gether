<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowGuestResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShowGuestResponse>
 */
class BookingNoShowGuestResponseFactory extends Factory
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
            'guest_user_id' => User::factory(),
            'response_type' => 'i_am_late',
            'message' => null,
            'new_arrival_time' => '23:30',
            'evidence_media_id' => null,
        ];
    }
}
