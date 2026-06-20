<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingGuestIntake;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingGuestIntake>
 */
class BookingGuestIntakeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'guest_user_id' => null,
            'booking_quote_id' => null,
            'booking_request_id' => null,
            'booking_id' => null,
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'status' => 'draft',
            'trip_purpose' => $this->faker->randomElement(['tourism', 'work', 'study', 'business_trip']),
            'trip_purpose_other' => null,
            'trip_purpose_visibility' => 'safe',
            'planned_arrival_date' => now()->addWeeks(2)->toDateString(),
            'planned_arrival_time' => '19:00',
            'planned_arrival_window' => null,
            'planned_departure_time' => '10:00',
            'needs_early_check_in' => false,
            'needs_late_check_out' => false,
            'luggage_amount' => null,
            'arrival_time_unknown' => false,
            'departure_time_unknown' => false,
            'early_check_in_requested' => false,
            'requested_early_check_in_time' => null,
            'late_check_in_requested' => false,
            'requested_late_check_in_time' => null,
            'late_check_out_requested' => false,
            'requested_late_check_out_time' => null,
            'early_check_out_requested' => false,
            'requested_early_check_out_time' => null,
            'can_adjust_arrival_time' => true,
            'baggage_level' => 'one_bag',
            'baggage_count' => 1,
            'has_large_suitcase' => false,
            'has_special_baggage' => false,
            'special_baggage_type' => null,
            'needs_luggage_storage_before_checkin' => false,
            'needs_luggage_storage_after_checkout' => false,
            'has_pet' => false,
            'pet_type' => null,
            'pet_size' => null,
            'pet_notes' => null,
            'smokes' => false,
            'smoking_type' => null,
            'accepts_smoking_rules' => true,
            'needs_quiet' => false,
            'needs_desk' => false,
            'noise_sensitivity_level' => null,
            'needs_workspace' => false,
            'needs_fast_wifi' => false,
            'needs_power_socket' => false,
            'needs_online_calls' => false,
            'needs_late_entry' => false,
            'needs_self_check_in' => false,
            'needs_registration' => false,
            'needs_work_documents' => false,
            'needs_invoice' => false,
            'needs_receipt' => false,
            'needs_contract' => false,
            'company_name' => null,
            'document_notes' => null,
            'special_requests' => null,
            'message_to_host' => null,
            'host_message' => null,
            'auto_generated_host_message' => null,
            'rules_accepted' => false,
            'rules_accepted_at' => null,
            'compatibility_checked_at' => null,
            'compatibility_status' => null,
            'compatibility_score' => null,
            'warnings_json' => [],
            'blocking_reasons_json' => [],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'rules_accepted' => true,
            'rules_accepted_at' => now(),
            'compatibility_checked_at' => now(),
            'compatibility_status' => 'ok',
            'compatibility_score' => 90,
        ]);
    }

    public function forBooking(Booking $booking): static
    {
        return $this->state(fn (): array => [
            'booking_id' => $booking->id,
            'user_id' => $booking->guest_user_id,
            'guest_user_id' => $booking->guest_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
        ]);
    }
}
