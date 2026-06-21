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
            'checkout_number' => sprintf('OUT-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'booking_stay_id' => null,
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'check_out_date' => now()->toDateString(),
            'planned_check_out_time' => '11:00',
            'check_out_window' => '10:00-11:00',
            'actual_check_out_at' => null,
            'check_out_method' => 'host_handoff',
            'keys_returned' => false,
            'keys_returned_count' => null,
            'access_card_returned' => false,
            'electronic_key_disabled' => false,
            'locker_emptied' => false,
            'locker_cleared' => false,
            'locker_key_returned' => false,
            'personal_items_taken' => false,
            'personal_items_removed' => false,
            'bedding_returned' => false,
            'towel_returned' => false,
            'sleeping_place_free' => false,
            'sleeping_place_cleared' => false,
            'room_checked' => false,
            'property_checked' => false,
            'sleeping_place_checked' => false,
            'has_damage' => false,
            'has_extra_dirt' => false,
            'has_extra_dirty' => false,
            'has_forgotten_items' => false,
            'has_lost_items' => false,
            'has_lost_key' => false,
            'has_inventory_issue' => false,
            'has_complaint' => false,
            'has_dispute' => false,
            'needs_deposit_deduction' => false,
            'deposit_review_required' => false,
            'deposit_deduction_requested' => false,
            'deposit_deduction_amount' => null,
            'deposit_deduction_reason' => null,
            'guest_comment' => null,
            'host_comment' => null,
            'internal_host_note' => null,
            'cleaning_required' => true,
            'inspection_required' => false,
            'repair_required' => false,
            'cleaning_task_id' => null,
            'maintenance_request_id' => null,
            'deposit_case_id' => null,
            'complaint_case_id' => null,
            'damage_photo_paths_json' => [],
            'guest_confirmed_at' => null,
            'host_confirmed_at' => null,
            'guest_preparing_at' => null,
            'guest_confirmed_checkout_at' => null,
            'host_notified_guest_checkout_at' => null,
            'host_confirmed_checkout_at' => null,
            'status' => 'not_started',
            'problem_status' => null,
            'last_reminder_sent_at' => null,
            'completed_at' => null,
            'closed_at' => null,
        ];
    }
}
