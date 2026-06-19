<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaitlistItem>
 */
class WaitlistItemFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = now()->addWeeks(2);

        return [
            'user_id' => User::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'desired_check_in' => $checkIn->toDateString(),
            'desired_check_out' => $checkIn->copy()->addDays(5)->toDateString(),
            'max_price' => 40,
            'ready_to_book' => true,
            'auto_request' => false,
            'notified' => false,
            'notified_at' => null,
            'status' => 'waiting',
        ];
    }
}
