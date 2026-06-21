<?php

namespace Database\Factories;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInStep>
 */
class BookingCheckInStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'step_key' => 'show_instruction',
            'status' => 'pending',
            'completed_by_user_id' => null,
            'completed_at' => null,
            'required' => true,
            'sort_order' => 10,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'completed_by_user_id' => User::factory(),
            'completed_at' => now(),
        ]);
    }
}
