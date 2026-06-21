<?php

namespace Database\Factories;

use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequest>
 */
class BookingRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = CarbonImmutable::now()->addDays(14)->startOfDay();
        $checkOut = $checkIn->addDays(3);

        return [
            'request_number' => 'BR-'.$checkIn->format('Y').'-'.$this->faker->unique()->numberBetween(1, 999999),
            'booking_quote_id' => BookingQuote::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'status' => BookingRequest::STATUS_SUBMITTED,
            'hold_dates' => true,
            'hold_expires_at' => now()->addDay(),
            'expires_at' => now()->addDay(),
            'check_in_date' => $checkIn->toDateString(),
            'check_in_time' => '15:00',
            'check_out_date' => $checkOut->toDateString(),
            'check_out_time' => '11:00',
            'nights_count' => 3,
            'chargeable_days_count' => 3,
            'calendar_presence_days_count' => 4,
            'guests_count' => 1,
            'trip_purpose' => 'work',
            'planned_arrival_time' => '18:00',
            'planned_departure_time' => '10:00',
            'guest_message' => $this->faker->sentence(),
            'has_baggage' => true,
            'needs_luggage_storage' => false,
            'needs_early_check_in' => false,
            'needs_late_checkout' => false,
            'needs_residence_registration' => false,
            'needs_reporting_documents' => false,
            'guest_agreed_to_rules' => true,
            'guest_agreed_to_cancellation_policy' => true,
            'guest_agreed_to_deposit_policy' => true,
            'guest_profile_snapshot_json' => [],
            'guest_rating_snapshot_json' => [],
            'compatibility_snapshot_json' => [],
            'price_snapshot_json' => [],
            'warnings_snapshot_json' => [],
            'total_amount' => 98,
            'deposit_amount' => 30,
            'cleaning_fee_amount' => 5,
            'service_fee_amount' => 3,
            'currency' => 'EUR',
        ];
    }

    public function preliminary(): static
    {
        return $this->state(fn (): array => [
            'request_type' => BookingRequest::TYPE_PRELIMINARY_INQUIRY,
            'hold_dates' => false,
            'hold_expires_at' => null,
        ]);
    }
}
