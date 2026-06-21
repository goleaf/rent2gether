<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingStatusService
{
    public function __construct(
        private readonly BookingLifecycleEventService $events,
        private readonly BookingCalendarIntegrationService $calendar,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(Booking $booking, string $newStatus, ?User $user = null, array $context = []): Booking
    {
        $oldStatus = $this->statusValue($booking);

        if ($oldStatus === $newStatus) {
            return $booking;
        }

        if (! $this->canTransition($booking, $newStatus)) {
            throw ValidationException::withMessages([
                'status' => __('bookings.validation.invalid_status_transition'),
            ]);
        }

        $booking->forceFill([
            ...$this->timestampAttributes($newStatus),
            'status' => $newStatus,
        ])->save();

        $booking->statusLogs()->create([
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? 'bookings.lifecycle.transitioned',
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        $booking->statusHistories()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by_user_id' => $user?->id,
            'note' => $context['reason_key'] ?? 'bookings.lifecycle.transitioned',
        ]);

        $this->events->record($booking->fresh(), $context['event_key'] ?? $this->eventKeyForStatus($newStatus), [
            'event_type' => $context['event_type'] ?? 'system',
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        if ($this->releasesLocks($newStatus)) {
            $this->calendar->releaseBookingLocks($booking, $newStatus);
        }

        return $booking->fresh();
    }

    public function canTransition(Booking $booking, string $newStatus): bool
    {
        $current = $this->statusValue($booking);

        return in_array($newStatus, $this->allowedTransitions()[$current] ?? [], true);
    }

    public function markConfirmed(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::Confirmed->value);
    }

    public function markPaid(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::Paid->value);
    }

    public function markReadyForCheckIn(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::ReadyForCheckInCore->value);
    }

    public function markCheckedIn(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::GuestCheckedIn->value);
    }

    public function markStayInProgress(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::StayInProgress->value);
    }

    public function markCheckOutSoon(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::CheckOutSoon->value);
    }

    public function markCheckedOut(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::GuestCheckedOut->value);
    }

    public function markWaitingInspection(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::WaitingPropertyInspection->value);
    }

    public function markWaitingDepositReturn(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::WaitingDepositReturn->value);
    }

    public function markCompleted(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::Completed->value);
    }

    public function markWaitingReview(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::WaitingReview->value);
    }

    public function markClosed(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::Closed->value);
    }

    public function markDisputed(Booking $booking): Booking
    {
        $booking->forceFill(['has_dispute' => true])->save();

        return $this->transition($booking, BookingStatus::DisputeOpened->value);
    }

    public function freezeUntilDisputeResolved(Booking $booking): Booking
    {
        $booking->forceFill(['has_dispute' => true])->save();

        return $this->transition($booking, BookingStatus::FrozenUntilDisputeResolved->value);
    }

    public function markNoShow(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::NoShow->value);
    }

    public function markHostUnresponsive(Booking $booking): Booking
    {
        return $this->transition($booking, BookingStatus::HostUnresponsive->value);
    }

    /**
     * @return array<string, list<string>>
     */
    private function allowedTransitions(): array
    {
        return [
            BookingStatus::Draft->value => [BookingStatus::Created->value],
            BookingStatus::Created->value => [
                BookingStatus::WaitingHostConfirmation->value,
                BookingStatus::WaitingPayment->value,
                BookingStatus::WaitingIdentityVerification->value,
                BookingStatus::WaitingDocumentVerification->value,
                BookingStatus::Confirmed->value,
                BookingStatus::RejectedByHost->value,
            ],
            BookingStatus::AwaitingHostApproval->value => [
                BookingStatus::WaitingHostConfirmation->value,
                BookingStatus::RejectedByHost->value,
                BookingStatus::WaitingPayment->value,
            ],
            BookingStatus::WaitingHostConfirmation->value => [
                BookingStatus::WaitingPayment->value,
                BookingStatus::Confirmed->value,
                BookingStatus::RejectedByHost->value,
                BookingStatus::WaitingGuestResponse->value,
            ],
            BookingStatus::WaitingGuestResponse->value => [
                BookingStatus::WaitingHostConfirmation->value,
                BookingStatus::WaitingPayment->value,
                BookingStatus::CancelledByGuestFlow->value,
            ],
            BookingStatus::AwaitingPayment->value => [
                BookingStatus::Paid->value,
                BookingStatus::Confirmed->value,
                BookingStatus::PaymentFailed->value,
            ],
            BookingStatus::WaitingPayment->value => [
                BookingStatus::Paid->value,
                BookingStatus::Confirmed->value,
                BookingStatus::PaymentFailed->value,
                BookingStatus::CancelledByGuestFlow->value,
            ],
            BookingStatus::WaitingIdentityVerification->value => [
                BookingStatus::WaitingDocumentVerification->value,
                BookingStatus::WaitingPayment->value,
                BookingStatus::Confirmed->value,
                BookingStatus::CancelledByGuestFlow->value,
                BookingStatus::CancelledByServiceFuture->value,
            ],
            BookingStatus::WaitingDocumentVerification->value => [
                BookingStatus::WaitingPayment->value,
                BookingStatus::Confirmed->value,
                BookingStatus::CancelledByGuestFlow->value,
                BookingStatus::CancelledByServiceFuture->value,
            ],
            BookingStatus::Paid->value => [BookingStatus::Confirmed->value],
            BookingStatus::Confirmed->value => [
                BookingStatus::ReadyForCheckInCore->value,
                BookingStatus::GuestCheckedIn->value,
                BookingStatus::CancelledByGuestFlow->value,
                BookingStatus::CancelledByHostFlow->value,
                BookingStatus::DisputeOpened->value,
            ],
            BookingStatus::ReadyForCheckInCore->value => [
                BookingStatus::GuestCheckedIn->value,
                BookingStatus::NoShow->value,
                BookingStatus::HostUnresponsive->value,
                BookingStatus::CancelledByGuestFlow->value,
            ],
            BookingStatus::GuestCheckedIn->value => [BookingStatus::StayInProgress->value],
            BookingStatus::StayInProgress->value => [
                BookingStatus::CheckOutSoon->value,
                BookingStatus::GuestCheckedOut->value,
                BookingStatus::DisputeOpened->value,
                BookingStatus::HostUnresponsive->value,
            ],
            BookingStatus::CheckOutSoon->value => [BookingStatus::GuestCheckedOut->value],
            BookingStatus::GuestCheckedOut->value => [
                BookingStatus::WaitingPropertyInspection->value,
                BookingStatus::WaitingDepositReturn->value,
                BookingStatus::WaitingReview->value,
                BookingStatus::Completed->value,
            ],
            BookingStatus::WaitingPropertyInspection->value => [
                BookingStatus::WaitingDepositReturn->value,
                BookingStatus::WaitingReview->value,
                BookingStatus::Completed->value,
            ],
            BookingStatus::WaitingDepositReturn->value => [
                BookingStatus::WaitingReview->value,
                BookingStatus::Completed->value,
            ],
            BookingStatus::WaitingReview->value => [BookingStatus::Completed->value],
            BookingStatus::Completed->value => [BookingStatus::Closed->value],
            BookingStatus::DisputeOpened->value => [BookingStatus::FrozenUntilDisputeResolved->value],
            BookingStatus::FrozenUntilDisputeResolved->value => [BookingStatus::Closed->value],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampAttributes(string $newStatus): array
    {
        return match ($newStatus) {
            BookingStatus::Paid->value => [
                'paid_at' => now(),
                'payment_paid_at' => now(),
            ],
            BookingStatus::GuestCheckedIn->value => [
                'guest_check_in_confirmed_at' => now(),
                'guest_checked_in_at' => now(),
                'checked_in_at' => now(),
            ],
            BookingStatus::StayInProgress->value => [
                'stay_started_at' => now(),
            ],
            BookingStatus::GuestCheckedOut->value => [
                'guest_check_out_confirmed_at' => now(),
                'guest_checked_out_at' => now(),
                'checked_out_at' => now(),
                'stay_ended_at' => now(),
            ],
            BookingStatus::Closed->value => [
                'closed_at' => now(),
            ],
            default => [],
        };
    }

    private function eventKeyForStatus(string $status): string
    {
        return match ($status) {
            BookingStatus::WaitingPayment->value => 'payment_started',
            BookingStatus::Paid->value => 'payment_completed',
            BookingStatus::Confirmed->value => 'confirmed',
            BookingStatus::ReadyForCheckInCore->value => 'ready_for_check_in',
            BookingStatus::GuestCheckedIn->value => 'guest_checked_in',
            BookingStatus::StayInProgress->value => 'stay_started',
            BookingStatus::CheckOutSoon->value => 'check_out_soon',
            BookingStatus::GuestCheckedOut->value => 'guest_checked_out',
            BookingStatus::WaitingPropertyInspection->value => 'inspection_started',
            BookingStatus::WaitingDepositReturn->value => 'deposit_return_started',
            BookingStatus::WaitingReview->value => 'review_requested',
            BookingStatus::Completed->value => 'completed',
            BookingStatus::Closed->value => 'closed',
            BookingStatus::RejectedByHost->value => 'host_rejected',
            BookingStatus::PaymentFailed->value => 'payment_failed',
            BookingStatus::NoShow->value => 'no_show',
            BookingStatus::HostUnresponsive->value => 'host_unresponsive',
            BookingStatus::DisputeOpened->value => 'dispute_opened',
            default => 'created',
        };
    }

    private function releasesLocks(string $status): bool
    {
        return in_array($status, [
            BookingStatus::RejectedByHost->value,
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::CancelledByServiceFuture->value,
            BookingStatus::PaymentFailed->value,
        ], true);
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
    }
}
