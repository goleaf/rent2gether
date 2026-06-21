<?php

namespace Database\Factories;

use App\Models\BookingQuote;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingQuote>
 */
class BookingQuoteFactory extends Factory
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
            'quote_number' => 'QT-'.$checkIn->format('Y').'-'.$this->faker->numberBetween(1, 999999).'-'.strtolower($this->faker->lexify('????')),
            'user_id' => User::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'host_user_id' => User::factory()->host(),
            'rental_mode' => 'nightly',
            'check_in_date' => $checkIn->toDateString(),
            'check_in_time' => '15:00',
            'check_out_date' => $checkOut->toDateString(),
            'check_out_time' => '11:00',
            'nights_count' => 3,
            'chargeable_days_count' => 3,
            'calendar_presence_days_count' => 4,
            'guests_count' => 1,
            'included_guests_count' => 1,
            'extra_guests_count' => 0,
            'availability_status' => 'available',
            'validation_status' => 'valid',
            'pricing_status' => 'calculated',
            'accommodation_amount' => 60,
            'discount_amount' => 0,
            'cleaning_fee_amount' => 5,
            'service_fee_amount' => 3,
            'tax_amount' => 0,
            'city_fee_amount' => 0,
            'deposit_amount' => 30,
            'total_without_deposit' => 68,
            'total_payable' => 98,
            'host_payout_preview_amount' => 65,
            'refundable_amount' => 30,
            'non_refundable_amount' => 68,
            'currency' => 'EUR',
            'payment_deadline_at' => now()->addMinutes(20),
            'expires_at' => now()->addMinutes(20),
            'status' => BookingQuote::STATUS_VALID,
        ];
    }
}
