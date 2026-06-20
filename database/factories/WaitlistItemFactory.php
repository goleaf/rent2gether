<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaitlistItem>
 */
class WaitlistItemFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = CarbonImmutable::now()->addWeeks(2)->startOfDay();
        $checkOut = $checkIn->addDays(5);

        return [
            'user_id' => User::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'desired_check_in' => $checkIn->toDateString(),
            'desired_check_out' => $checkOut->toDateString(),
            'desired_check_in_date' => $checkIn->toDateString(),
            'desired_check_out_date' => $checkOut->toDateString(),
            'nights_count' => 5,
            'calendar_days_count' => 6,
            'guests_count' => 1,
            'max_price' => 40,
            'max_price_per_night' => 40,
            'max_total_price' => 250,
            'max_deposit' => 50,
            'currency' => 'EUR',
            'price_at_join' => 45,
            'ready_to_book' => true,
            'ready_to_book_immediately' => true,
            'ready_to_pay_immediately' => false,
            'auto_request' => false,
            'auto_send_request' => false,
            'auto_create_booking_draft' => false,
            'notify_available' => true,
            'notify_price_drop' => true,
            'notify_similar_available' => false,
            'notify_offer_expiring' => true,
            'quiet_hours_enabled' => true,
            'position' => null,
            'priority_score' => 0,
            'offered_count' => 0,
            'skipped_count' => 0,
            'max_skips' => 3,
            'added_at' => now(),
            'notified' => false,
            'notified_at' => null,
            'status' => 'active',
        ];
    }
}
