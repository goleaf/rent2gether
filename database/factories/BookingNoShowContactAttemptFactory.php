<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowContactAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShowContactAttempt>
 */
class BookingNoShowContactAttemptFactory extends Factory
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
            'attempted_by_user_id' => User::factory(),
            'contact_channel' => 'in_app',
            'attempt_type' => 'guest_check_request',
            'status' => 'sent',
            'message_key' => 'no_show.messages.guest_response_required',
            'message_text' => null,
            'attempted_at' => now(),
        ];
    }
}
