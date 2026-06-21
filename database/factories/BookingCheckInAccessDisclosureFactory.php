<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAccessDisclosure;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInAccessDisclosure>
 */
class BookingCheckInAccessDisclosureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'disclosure_type' => 'exact_address',
            'shown_at' => now(),
            'shown_by_user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Bulk seed',
        ];
    }
}
