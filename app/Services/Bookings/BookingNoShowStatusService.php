<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\User;

class BookingNoShowStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingNoShow $noShow, string $newStatus, ?User $user = null, array $context = []): BookingNoShow
    {
        $oldStatus = (string) $noShow->status;

        if ($oldStatus === $newStatus) {
            return $noShow;
        }

        if (! $this->canTransition($noShow, $newStatus)) {
            abort(422, __('no_show.validation.invalid_status_transition'));
        }

        $noShow->forceFill([
            ...$this->timestampAttributes($newStatus),
            'status' => $newStatus,
        ])->save();

        $noShow->statusLogs()->create([
            'booking_id' => $noShow->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $noShow->fresh();
    }

    public function canTransition(BookingNoShow $noShow, string $newStatus): bool
    {
        if ((string) $noShow->status === $newStatus) {
            return true;
        }

        return ! in_array((string) $noShow->status, ['closed', 'cancelled'], true);
    }

    public function syncBookingStatus(BookingNoShow $noShow): Booking
    {
        $booking = $noShow->booking()->firstOrFail();

        if ($noShow->status === 'confirmed_no_show') {
            $booking->forceFill([
                'status' => BookingStatus::NoShow,
                'refund_amount' => $noShow->refund_amount,
                'refund_status' => $noShow->refund_or_penalty_status === 'refund_created' ? 'pending' : $booking->refund_status,
                'payment_status' => $this->paymentStatusAfterNoShow($booking, $noShow),
            ])->save();
        }

        if ($noShow->status === 'dispute_opened') {
            $booking->forceFill([
                'status' => BookingStatus::DisputeOpened,
                'has_dispute' => true,
            ])->save();
        }

        if ($noShow->status === 'converted_to_host_unresponsive') {
            $booking->forceFill(['status' => BookingStatus::HostUnresponsive])->save();
        }

        return $booking->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampAttributes(string $newStatus): array
    {
        return match ($newStatus) {
            'waiting_period_expired' => ['waiting_expired_at' => now()],
            'confirmed_no_show', 'rejected_no_show', 'dispute_opened' => ['decision_at' => now()],
            'calendar_released' => ['dates_released_at' => now()],
            'closed' => ['closed_at' => now()],
            default => [],
        };
    }

    private function paymentStatusAfterNoShow(Booking $booking, BookingNoShow $noShow): string
    {
        $paid = (float) ($booking->total_payable ?: $booking->total_amount ?: $booking->total);
        $refund = (float) $noShow->refund_amount;

        if ($refund <= 0.0) {
            return $booking->payment_status instanceof \BackedEnum ? $booking->payment_status->value : (string) $booking->payment_status;
        }

        return $refund >= $paid ? 'refunded_full' : 'refunded_partial';
    }
}
