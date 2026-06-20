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
            'booking_id' => Booking::factory(),
            'current_checkout_date' => now()->addDays(7)->toDateString(),
            'requested_new_checkout_date' => now()->addDays(7 + $extraNights)->toDateString(),
            'additional_nights' => $extraNights,
            'additional_amount' => $extraAmount,
            'original_check_out' => now()->addDays(7)->toDateString(),
            'new_check_out' => now()->addDays(7 + $extraNights)->toDateString(),
            'extra_nights' => $extraNights,
            'extra_amount' => $extraAmount,
            'discount_amount' => 0,
            'total_extra' => $extraAmount,
            'new_total' => $extraAmount,
            'payment_required' => $extraAmount > 0,
            'requires_host_approval' => true,
            'status' => BookingExtensionStatus::AwaitingHostApproval,
        ];
    }
}
