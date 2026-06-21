<?php

namespace Database\Factories;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutStep>
 */
class BookingCheckOutStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'step_key' => 'guest_confirm_checkout',
            'status' => 'pending',
            'required' => true,
            'completed_by_user_id' => null,
            'completed_at' => null,
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
