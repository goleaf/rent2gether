<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveContactAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsiveContactAttempt>
 */
class HostUnresponsiveContactAttemptFactory extends Factory
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
            'target_user_id' => User::factory(),
            'target_type' => 'host',
            'target_name_snapshot' => $this->faker->name(),
            'target_contact_snapshot' => $this->faker->safeEmail(),
            'contact_channel' => 'in_app',
            'attempt_type' => 'urgent_check_in_alert',
            'status' => 'sent',
            'message_key' => 'host_unresponsive.notifications.urgent_host.title',
            'attempted_at' => now(),
        ];
    }
}
