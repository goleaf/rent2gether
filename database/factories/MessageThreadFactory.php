<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\MessageThread;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageThread>
 */
class MessageThreadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'last_message_at' => now(),
            'status' => 'open',
        ];
    }
}
