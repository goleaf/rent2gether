<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsiveMedia>
 */
class HostUnresponsiveMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host_unresponsive_case_id' => BookingHostUnresponsiveCase::factory(),
            'booking_id' => Booking::factory(),
            'uploaded_by_user_id' => User::factory(),
            'media_type' => 'photo',
            'media_role' => 'guest_waiting_evidence',
            'path' => 'host-unresponsive/'.$this->faker->uuid().'.jpg',
            'visibility' => 'guest_and_host',
        ];
    }
}
