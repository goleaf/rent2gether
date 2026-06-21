<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPriceSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPriceSnapshot>
 */
class BookingPriceSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'booking_quote_id' => null,
            'pricing_settings_snapshot_json' => [],
            'quote_lines_snapshot_json' => [],
            'discounts_snapshot_json' => [],
            'promo_code_snapshot_json' => null,
            'accommodation_before_discount' => 60,
            'discount_amount' => 0,
            'accommodation_after_discount' => 60,
            'early_check_in_fee' => 0,
            'late_checkout_fee' => 0,
            'extra_guest_fee' => 0,
            'cleaning_fee' => 10,
            'guest_service_fee' => 3,
            'host_service_fee' => 0,
            'tax_amount' => 0,
            'city_fee' => 0,
            'deposit_amount' => 50,
            'total_without_deposit' => 73,
            'total_payable' => 123,
            'host_payout_amount' => 70,
            'refundable_amount' => 50,
            'non_refundable_amount' => 73,
            'currency' => 'EUR',
        ];
    }
}
