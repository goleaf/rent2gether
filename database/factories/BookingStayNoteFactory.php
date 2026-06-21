<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\BookingStayNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStayNote>
 */
class BookingStayNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_stay_id' => BookingStay::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'note_type' => 'host_note',
            'visibility' => 'host_only',
            'note' => fake()->sentence(),
        ];
    }
}
