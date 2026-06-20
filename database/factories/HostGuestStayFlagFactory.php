<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\HostGuestStayFlag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostGuestStayFlag>
 */
class HostGuestStayFlagFactory extends Factory
{
    public function definition(): array
    {
        $flagKey = $this->faker->randomElement([
            'payment_pending',
            'checkout_today',
            'special_request',
        ]);

        return [
            'user_id' => User::factory(),
            'guest_user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'flag_key' => $flagKey,
            'status' => 'open',
            'severity' => 'medium',
            'message_key' => 'current_occupants.flags.'.$flagKey,
            'message_params_json' => [],
            'resolved_at' => null,
        ];
    }
}
