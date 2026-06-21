<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingNoShow;

class BookingNoShowCalculatorService
{
    public function __construct(
        private readonly BookingNoShowPolicySnapshotService $snapshots,
    ) {}

    /**
     * @return array<string, float|string>
     */
    public function calculateRefundAndPenalty(BookingNoShow $noShow): array
    {
        $noShow->loadMissing('booking');
        $booking = $noShow->booking;
        $snapshot = $this->snapshots->getForBooking($booking);

        $depositRefund = $snapshot->refund_deposit_on_no_show ? $this->money($this->amount($booking, 'deposit_amount', 'deposit')) : 0.0;
        $cleaningRefund = $snapshot->refund_cleaning_fee_on_no_show ? $this->money($this->amount($booking, 'cleaning_fee_amount', 'cleaning_fee')) : 0.0;
        $serviceRefund = $snapshot->refund_service_fee_on_no_show ? $this->money($this->amount($booking, 'service_fee_amount', 'service_fee')) : 0.0;
        $penalty = $this->calculateGuestPenalty($noShow);
        $hostPayout = $this->calculateHostPayout($noShow);
        $refund = $this->money($depositRefund + $cleaningRefund + $serviceRefund);

        return [
            'refund_amount' => $refund,
            'penalty_amount' => $penalty,
            'deposit_refund_amount' => $depositRefund,
            'cleaning_fee_refund_amount' => $cleaningRefund,
            'service_fee_refund_amount' => $serviceRefund,
            'host_payout_amount' => $hostPayout,
            'currency' => strtoupper((string) ($booking->currency ?: $noShow->currency ?: 'EUR')),
        ];
    }

    public function calculateDepositRefund(BookingNoShow $noShow): float
    {
        return $this->calculateRefundAndPenalty($noShow)['deposit_refund_amount'];
    }

    public function calculateCleaningFeeRefund(BookingNoShow $noShow): float
    {
        return $this->calculateRefundAndPenalty($noShow)['cleaning_fee_refund_amount'];
    }

    public function calculateServiceFeeRefund(BookingNoShow $noShow): float
    {
        return $this->calculateRefundAndPenalty($noShow)['service_fee_refund_amount'];
    }

    public function calculateGuestPenalty(BookingNoShow $noShow): float
    {
        $noShow->loadMissing('booking');
        $booking = $noShow->booking;
        $snapshot = $this->snapshots->getForBooking($booking);

        return match ($snapshot->guest_penalty_rule) {
            'none' => 0.0,
            'first_night', 'policy_based' => $this->firstNightAmount($booking),
            default => $this->firstNightAmount($booking),
        };
    }

    public function calculateHostPayout(BookingNoShow $noShow): float
    {
        $noShow->loadMissing('booking');
        $booking = $noShow->booking;
        $snapshot = $this->snapshots->getForBooking($booking);

        return match ($snapshot->host_payout_rule) {
            'none' => 0.0,
            'first_night', 'full_first_day', 'policy_based' => $this->firstNightAmount($booking),
            default => $this->firstNightAmount($booking),
        };
    }

    private function firstNightAmount(Booking $booking): float
    {
        $nights = max(1, (int) ($booking->nights_count ?: $booking->nights ?: 1));
        $accommodation = $this->amount($booking, 'accommodation_amount', 'subtotal_amount');

        if ($accommodation <= 0.0) {
            $accommodation = (float) $booking->subtotal;
        }

        return $this->money($accommodation / $nights);
    }

    private function amount(Booking $booking, string $primary, string $fallback): float
    {
        $primaryValue = (float) $booking->{$primary};

        return $primaryValue !== 0.0 ? $primaryValue : (float) $booking->{$fallback};
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
