<?php

namespace Database\Factories;

use App\Enums\BookingExtensionStatus;
use App\Models\Booking;
use App\Models\BookingExtension;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingExtension>
 */
class BookingExtensionFactory extends Factory
{
    public function definition(): array
    {
        $extraNights = $this->faker->numberBetween(1, 7);
        $extraAmount = $this->faker->randomFloat(2, 10, 30) * $extraNights;

        return [
            'extension_number' => sprintf('EXT-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'booking_stay_id' => null,
            'guest_user_id' => null,
            'host_user_id' => null,
            'property_id' => null,
            'room_id' => null,
            'sleeping_place_id' => null,
            'booking_quote_id' => null,
            'booking_payment_id' => null,
            'current_checkout_date' => now()->addDays(7)->toDateString(),
            'requested_new_checkout_date' => now()->addDays(7 + $extraNights)->toDateString(),
            'current_check_out_date' => now()->addDays(7)->toDateString(),
            'new_check_out_date' => now()->addDays(7 + $extraNights)->toDateString(),
            'additional_nights' => $extraNights,
            'additional_nights_count' => $extraNights,
            'additional_chargeable_days_count' => $extraNights,
            'additional_calendar_presence_days_count' => $extraNights + 1,
            'additional_amount' => $extraAmount,
            'original_check_out' => now()->addDays(7)->toDateString(),
            'new_check_out' => now()->addDays(7 + $extraNights)->toDateString(),
            'extra_nights' => $extraNights,
            'extra_amount' => $extraAmount,
            'discount_amount' => 0,
            'total_extra' => $extraAmount,
            'new_total' => $extraAmount,
            'extension_type' => 'host_approval_extension',
            'payment_required' => $extraAmount > 0,
            'requires_payment' => $extraAmount > 0,
            'payment_status' => $extraAmount > 0 ? 'waiting_payment' : 'not_required',
            'requires_host_approval' => true,
            'requires_host_confirmation' => true,
            'payment_method' => null,
            'accommodation_amount' => $extraAmount,
            'service_fee_amount' => 0,
            'cleaning_fee_amount' => 0,
            'additional_deposit_amount' => 0,
            'total_payable' => $extraAmount,
            'host_payout_amount' => $extraAmount,
            'refundable_amount' => 0,
            'non_refundable_amount' => $extraAmount,
            'currency' => 'EUR',
            'hold_dates' => true,
            'status' => BookingExtensionStatus::AwaitingHostApproval,
        ];
    }
}
