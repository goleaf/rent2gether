<?php

namespace Database\Factories;

use App\Models\BookingNoShowPolicy;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShowPolicy>
 */
class BookingNoShowPolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'waiting_period_minutes' => 180,
            'same_day_waiting_period_minutes' => 60,
            'night_arrival_waiting_period_minutes' => 240,
            'hold_first_night_on_no_show' => true,
            'release_remaining_nights_after_no_show' => true,
            'refund_deposit_on_no_show' => true,
            'refund_cleaning_fee_on_no_show' => true,
            'refund_service_fee_on_no_show' => false,
            'host_payout_rule' => 'policy_based',
            'guest_penalty_rule' => 'policy_based',
            'active' => true,
        ];
    }
}
