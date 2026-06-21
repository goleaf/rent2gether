<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingCancellationService
{
    public function __construct(
        private readonly BookingCancellationNumberService $numbers,
        private readonly BookingCancellationPreviewService $previews,
        private readonly CancellationPolicySnapshotService $snapshots,
        private readonly BookingCancellationRefundService $refunds,
        private readonly BookingCancellationCalendarService $calendar,
        private readonly BookingCancellationEventService $events,
        private readonly BookingCancellationStatusService $statuses,
        private readonly BookingCancellationNotificationService $notifications,
    ) {}

    public function confirmCancellation(User $user, BookingCancellationPreview $preview): BookingCancellation
    {
        $preview->loadMissing('booking');

        if ($preview->status !== 'calculated' || ($preview->expires_at && $preview->expires_at->isPast())) {
            throw ValidationException::withMessages([
                'preview' => __('cancellations.validation.preview_expired'),
            ]);
        }

        return DB::transaction(function () use ($user, $preview): BookingCancellation {
            $booking = $preview->booking;
            $snapshot = $this->snapshots->getForBooking($booking);

            $cancellation = BookingCancellation::query()->create([
                'cancellation_number' => $this->numbers->generateCancellationNumber(),
                'booking_id' => $booking->id,
                'booking_cancellation_preview_id' => $preview->id,
                'guest_user_id' => $preview->guest_user_id,
                'host_user_id' => $preview->host_user_id,
                'property_id' => $preview->property_id,
                'room_id' => $preview->room_id,
                'sleeping_place_id' => $preview->sleeping_place_id,
                'cancelled_by_user_id' => $user->id,
                'cancelled_by_type' => $preview->requested_by_type,
                'cancellation_type' => $preview->cancellation_type,
                'reason_key' => $preview->reason_key,
                'comment' => $preview->comment,
                'status' => 'confirmed',
                'check_in_date' => $preview->check_in_date,
                'check_out_date' => $preview->check_out_date,
                'cancelled_at' => now(),
                'hours_before_check_in' => $preview->hours_before_check_in,
                'nights_before_check_in' => $preview->nights_before_check_in,
                'nights_used' => $preview->nights_used,
                'nights_unused' => $preview->nights_unused,
                'policy_snapshot_id' => $snapshot->id,
                'accommodation_amount' => $preview->accommodation_amount,
                'cleaning_fee_amount' => $preview->cleaning_fee_amount,
                'service_fee_amount' => $preview->service_fee_amount,
                'deposit_amount' => $preview->deposit_amount,
                'tax_amount' => $preview->tax_amount,
                'city_fee_amount' => $preview->city_fee_amount,
                'accommodation_refund_amount' => $preview->accommodation_refund_amount,
                'cleaning_fee_refund_amount' => $preview->cleaning_fee_refund_amount,
                'service_fee_refund_amount' => $preview->service_fee_refund_amount,
                'deposit_refund_amount' => $preview->deposit_refund_amount,
                'tax_refund_amount' => $preview->tax_refund_amount,
                'city_fee_refund_amount' => $preview->city_fee_refund_amount,
                'penalty_amount' => $preview->penalty_amount,
                'host_payout_adjustment_amount' => $preview->host_payout_adjustment_amount,
                'total_refund_amount' => $preview->total_refund_amount,
                'total_non_refundable_amount' => $preview->total_non_refundable_amount,
                'currency' => $preview->currency,
                'refund_status' => (float) $preview->total_refund_amount > 0 ? 'pending' : 'not_required',
                'calendar_release_status' => 'pending',
                'requires_host_response' => false,
                'requires_dispute' => in_array($preview->reason_key, ['housing_problem', 'host_unresponsive', 'listing_mismatch'], true),
            ]);

            $preview->forceFill(['status' => 'converted_to_cancellation'])->save();

            $this->refunds->createRefundLines($cancellation->load('preview'));
            $this->refunds->createRefundFromCancellation($cancellation);
            $this->updateBookingAfterCancellation($booking, $cancellation);
            $this->calendar->releaseCalendarLocks($cancellation);
            $this->statuses->transition($cancellation->fresh(), 'booking_cancelled', $user, ['reason_key' => $cancellation->reason_key]);
            $this->events->record($cancellation->fresh(), 'cancellation_confirmed', ['preview_id' => $preview->id]);
            $this->events->record($cancellation->fresh(), 'booking_cancelled');
            $this->notifications->notifyGuestBookingCancelled($cancellation->fresh());
            $this->notifications->notifyHostBookingCancelled($cancellation->fresh());

            return $cancellation->fresh(['refundLines', 'bookingRefund']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function cancelBooking(User $user, Booking $booking, array $data): BookingCancellation
    {
        if ($this->bookingIsPaid($booking) && empty($data['booking_cancellation_preview_id'])) {
            throw ValidationException::withMessages([
                'preview' => __('cancellations.validation.preview_required'),
            ]);
        }

        if (! $this->bookingIsPaid($booking)) {
            return $this->cancelBeforePayment($user, $booking, $data);
        }

        $preview = BookingCancellationPreview::query()->findOrFail($data['booking_cancellation_preview_id']);

        return $this->confirmCancellation($user, $preview);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function cancelBeforePayment(User $user, Booking $booking, array $data): BookingCancellation
    {
        $preview = $this->previews->createPreview($user, $booking, [
            ...$data,
            'cancellation_type' => $data['cancellation_type'] ?? 'before_payment',
        ]);

        return $this->confirmCancellation($user, $preview);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function cancelAfterPayment(User $user, Booking $booking, array $data): BookingCancellation
    {
        return $this->cancelBooking($user, $booking, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function cancelAfterCheckIn(User $user, Booking $booking, array $data): BookingCancellation
    {
        $preview = $this->previews->createPreview($user, $booking, [
            ...$data,
            'cancellation_type' => $data['cancellation_type'] ?? 'early_termination',
        ]);

        return $this->confirmCancellation($user, $preview);
    }

    public function closeCancellation(BookingCancellation $cancellation): BookingCancellation
    {
        $cancellation->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
        ])->save();

        $this->events->record($cancellation, 'cancellation_closed');

        return $cancellation->fresh();
    }

    private function updateBookingAfterCancellation(Booking $booking, BookingCancellation $cancellation): void
    {
        $status = $cancellation->cancelled_by_type === 'host'
            ? BookingStatus::CancelledByHostFlow
            : BookingStatus::CancelledByGuestFlow;

        $booking->forceFill([
            'status' => $status,
            'cancelled_by_user_id' => $cancellation->cancelled_by_user_id,
            'cancelled_by_type' => $cancellation->cancelled_by_type,
            'cancelled_by' => $cancellation->cancelled_by_type,
            'cancel_reason' => $cancellation->reason_key,
            'cancellation_reason' => $cancellation->reason_key,
            'cancelled_at' => $cancellation->cancelled_at,
            'refund_amount' => $cancellation->total_refund_amount,
            'refund_status' => $cancellation->refund_status,
            'payment_status' => $this->paymentStatusAfterCancellation($booking, $cancellation),
        ])->save();
    }

    private function bookingIsPaid(Booking $booking): bool
    {
        $status = $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status->value
            : (string) $booking->payment_status;

        return in_array($status, ['paid', 'partially_paid', 'refunded_partial', 'refunded_full'], true);
    }

    private function paymentStatusAfterCancellation(Booking $booking, BookingCancellation $cancellation): string
    {
        $paid = (float) ($booking->total_payable ?: $booking->total_amount ?: $booking->total);
        $refund = (float) $cancellation->total_refund_amount;

        if ($refund <= 0.0) {
            return $booking->payment_status instanceof PaymentStatus ? $booking->payment_status->value : (string) $booking->payment_status;
        }

        return $refund >= $paid ? PaymentStatus::RefundedFull->value : PaymentStatus::RefundedPartial->value;
    }
}
