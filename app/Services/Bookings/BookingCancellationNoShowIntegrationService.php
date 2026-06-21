<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;

class BookingCancellationNoShowIntegrationService
{
    public function createFromNoShow($noShowCase): BookingCancellation
    {
        return app(BookingCancellationService::class)->cancelBooking($noShowCase->host, $noShowCase->booking, [
            'cancellation_type' => 'no_show_related',
            'reason_key' => 'no_show_related',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function calculateNoShowRefund(BookingCancellation $cancellation): array
    {
        return [
            'refund_amount' => (float) $cancellation->deposit_refund_amount,
            'reason_key' => 'no_show_related',
        ];
    }
}
