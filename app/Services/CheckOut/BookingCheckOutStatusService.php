<?php

namespace App\Services\CheckOut;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutStatusLog;
use App\Models\BookingStay;
use App\Models\User;

class BookingCheckOutStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingCheckOut $checkOut, string $newStatus, ?User $user = null, array $context = []): BookingCheckOut
    {
        if (! $this->canTransition($checkOut, $newStatus)) {
            return $checkOut->refresh();
        }

        $oldStatus = $checkOut->status;
        $checkOut->forceFill(['status' => $newStatus])->save();

        BookingCheckOutStatusLog::query()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $checkOut->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        $this->syncBookingStatus($checkOut->refresh());
        $this->syncStayStatus($checkOut->refresh());

        return $checkOut->refresh();
    }

    public function canTransition(BookingCheckOut $checkOut, string $newStatus): bool
    {
        if (in_array($checkOut->status, ['closed', 'cancelled'], true)) {
            return $newStatus === $checkOut->status;
        }

        return in_array($newStatus, $this->statuses(), true);
    }

    public function syncBookingStatus(BookingCheckOut $checkOut): Booking
    {
        $booking = $checkOut->booking()->firstOrFail();
        $payload = match ($checkOut->status) {
            'guest_checked_out', 'host_notified_guest_checked_out', 'waiting_host_confirmation' => [
                'status' => BookingStatus::GuestCheckedOut,
                'guest_checked_out_at' => $booking->guest_checked_out_at ?: $checkOut->guest_confirmed_checkout_at ?: $checkOut->guest_confirmed_at ?: now(),
                'checked_out_at' => $booking->checked_out_at ?: $checkOut->actual_check_out_at ?: now(),
            ],
            'waiting_inspection', 'inspection_in_progress', 'inspection_completed' => [
                'status' => BookingStatus::WaitingPropertyInspection,
            ],
            'deposit_review_required', 'deposit_return_started', 'deposit_deduction_requested' => [
                'status' => BookingStatus::WaitingDepositReturn,
            ],
            'completed' => [
                'status' => BookingStatus::Completed,
                'checked_out_at' => $booking->checked_out_at ?: $checkOut->actual_check_out_at ?: now(),
            ],
            'closed' => [
                'status' => BookingStatus::Closed,
            ],
            default => [],
        };

        if ($payload !== []) {
            $booking->forceFill($payload)->save();
        }

        return $booking->refresh();
    }

    public function syncStayStatus(BookingCheckOut $checkOut): ?BookingStay
    {
        $stay = $checkOut->stay ?: $checkOut->booking?->stay;

        if (! $stay) {
            return null;
        }

        $payload = match ($checkOut->status) {
            'guest_preparing' => ['status' => 'checkout_started', 'checkout_required' => true],
            'guest_checked_out', 'host_notified_guest_checked_out', 'waiting_host_confirmation' => [
                'status' => 'guest_checked_out',
                'actual_check_out_at' => $checkOut->actual_check_out_at ?: now(),
                'ended_at' => $checkOut->actual_check_out_at ?: now(),
            ],
            'waiting_inspection', 'inspection_in_progress', 'inspection_completed' => ['status' => 'waiting_inspection'],
            'completed' => ['status' => 'completed', 'ended_at' => $checkOut->completed_at ?: now()],
            'closed' => ['status' => 'closed', 'closed_at' => $checkOut->closed_at ?: now()],
            default => [],
        };

        if ($payload !== []) {
            $stay->forceFill($payload)->save();
        }

        return $stay->refresh();
    }

    /**
     * @return list<string>
     */
    private function statuses(): array
    {
        return [
            'not_started',
            'scheduled',
            'reminder_sent',
            'checkout_soon',
            'waiting_guest_checkout',
            'guest_preparing',
            'guest_checked_out',
            'host_notified_guest_checked_out',
            'waiting_host_confirmation',
            'waiting_inspection',
            'inspection_in_progress',
            'inspection_completed',
            'cleaning_required',
            'cleaning_scheduled',
            'cleaning_completed',
            'deposit_review_required',
            'deposit_return_started',
            'deposit_returned',
            'deposit_deduction_requested',
            'problem_reported',
            'dispute_opened',
            'completed',
            'closed',
            'cancelled',
        ];
    }
}
