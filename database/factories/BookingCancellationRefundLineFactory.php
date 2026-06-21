<?php

namespace Database\Factories;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationRefundLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCancellationRefundLine>
 */
class BookingCancellationRefundLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_cancellation_id' => BookingCancellation::factory(),
            'line_type' => 'accommodation',
            'label_key' => 'cancellations.refund_line_types.accommodation',
            'amount' => 100,
            'currency' => 'EUR',
            'refundable' => true,
            'refund_amount' => 100,
            'non_refundable_amount' => 0,
            'reason_key' => null,
            'sort_order' => 0,
        ];
    }
}
