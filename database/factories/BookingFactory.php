<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $checkOut = (clone $checkIn)->modify('+'.random_int(1, 14).' days');
        $nights = (int) $checkIn->diff($checkOut)->days;
        $pricePerNight = $this->faker->randomFloat(2, 10, 30);
        $subtotal = round($pricePerNight * $nights, 2);
        $serviceFee = round($subtotal * 0.05, 2);
        $cleaningFee = 5.00;
        $deposit = 30.00;
        $total = $subtotal + $cleaningFee + $deposit + $serviceFee;

        return [
            'bed_id' => Bed::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'booking_type' => BookingType::HostApproval->value,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'guests_count' => 1,
            'nights' => $nights,
            'nights_count' => $nights,
            'calendar_days_count' => $nights + 1,
            'price_per_night' => $pricePerNight,
            'subtotal' => $subtotal,
            'subtotal_amount' => $subtotal,
            'discount_amount' => 0,
            'cleaning_fee' => $cleaningFee,
            'cleaning_fee_amount' => $cleaningFee,
            'deposit' => $deposit,
            'deposit_amount' => $deposit,
            'service_fee' => $serviceFee,
            'service_fee_amount' => $serviceFee,
            'tax_amount' => 0,
            'city_fee_amount' => 0,
            'total' => $total,
            'total_amount' => $total,
            'refundable_amount' => $deposit,
            'non_refundable_amount' => $serviceFee,
            'currency' => 'EUR',
            'status' => BookingStatus::Confirmed->value,
            'payment_status' => PaymentStatus::Paid->value,
            'payment_method' => 'demo',
            'cancellation_policy' => CancellationPolicy::Flexible->value,
            'requires_document_check' => false,
            'requires_phone_check' => false,
            'requires_identity_check' => false,
            'refund_status' => 'none',
            'has_dispute' => false,
            'has_complaint' => false,
            'guest_review_left' => false,
            'host_review_left' => false,
        ];
    }
}
