<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\PlaceReadinessCheck;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlaceReadinessCheck>
 */
class PlaceReadinessCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'readiness_number' => sprintf('RDY-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'host_user_id' => User::factory(),
            'status' => 'checking',
            'check_reason' => 'before_check_in',
            'target_check_in_at' => now()->addHours(8),
            'checkout_completed' => false,
            'cleaning_completed' => false,
            'inspection_completed' => false,
            'repair_completed' => false,
            'inventory_ready' => false,
            'access_ready' => false,
            'deposit_review_not_blocking' => true,
            'complaint_not_blocking' => true,
            'calendar_available' => false,
            'same_day_turnover' => false,
            'gap_is_enough' => true,
        ];
    }
}
