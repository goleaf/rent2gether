<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOut>
 */
class BookingCheckOutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'check_out_date' => now()->toDateString(),
            'planned_check_out_time' => '11:00',
            'actual_check_out_at' => null,
            'check_out_method' => 'host_handoff',
            'keys_returned' => false,
            'keys_returned_count' => null,
            'access_card_returned' => false,
            'electronic_key_disabled' => false,
            'locker_emptied' => false,
            'locker_key_returned' => false,
            'personal_items_taken' => false,
            'bedding_returned' => false,
            'towel_returned' => false,
            'sleeping_place_free' => false,
            'room_checked' => false,
            'sleeping_place_checked' => false,
            'has_damage' => false,
            'has_extra_dirty' => false,
            'has_forgotten_items' => false,
            'needs_deposit_deduction' => false,
            'deposit_deduction_amount' => null,
            'deposit_deduction_reason' => null,
            'damage_photo_paths_json' => [],
            'guest_confirmed_at' => null,
            'host_confirmed_at' => null,
            'status' => 'not_started',
            'problem_status' => null,
            'last_reminder_sent_at' => null,
        ];
    }
}
