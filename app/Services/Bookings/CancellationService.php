<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundRequestStatus;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\PaymentRecord;
use App\Models\RefundRequest;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CancellationService
{
    public function __construct(
        private readonly RefundCalculator $refunds,
        private readonly AvailabilityService $availability,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * @return array{
     *     refund_amount:float,
     *     penalty_amount:float,
     *     deposit_refunded:bool,
     *     deposit_refund_amount:float,
     *     non_refundable_amount:float,
     *     explanation:string,
     *     reason:string,
     *     lines:list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool}>
     * }
     */
    public function calculateRefund(Booking $booking): array
    {
        return $this->refunds->calculate($booking)->toLegacyArray();
    }

    public function cancelByGuest(Booking $booking, ?string $reason = null, ?User $actor = null, ?string $details = null): bool
    {
        return $this->cancel($booking, BookingStatus::CancelledByGuest, 'guest', $reason, $actor, $details);
    }

    public function cancelByHost(Booking $booking, ?string $reason = null, ?User $actor = null, ?string $details = null): bool
    {
        return $this->cancel($booking, BookingStatus::CancelledByHost, 'host', $reason, $actor, $details);
    }

    private function cancel(
        Booking $booking,
        BookingStatus $targetStatus,
        string $cancelledBy,
        ?string $reason,
        ?User $actor,
        ?string $details,
    ): bool {
        $cancelled = false;

        DB::transaction(function () use ($booking, $targetStatus, $cancelledBy, $reason, $actor, $details, &$cancelled): void {
            $fresh = $this->bookingForCancellation($booking);

            if (! $fresh->isCancellable()) {
                return;
            }

            $estimate = $this->refunds->calculate($fresh, $cancelledBy);
            $fromStatus = $fresh->status instanceof BookingStatus ? $fresh->status->value : (string) $fresh->status;
            $paymentStatus = $this->paymentStatusAfterRefund($fresh, $estimate->paidAmount, $estimate->refundAmount);
            $now = now();

            Booking::query()
                ->whereKey($fresh->id)
                ->update([
                    'status' => $targetStatus->value,
                    'cancel_reason' => $reason,
                    'cancellation_reason' => $reason,
                    'cancelled_by' => $cancelledBy,
                    'cancelled_at' => $now,
                    'refund_amount' => $estimate->refundAmount,
                    'refund_status' => $estimate->refundAmount > 0.0 ? RefundRequestStatus::Requested->value : 'none',
                    'payment_status' => $paymentStatus->value,
                    'updated_at' => $now,
                ]);

            BookingStatusHistory::query()->create([
                'booking_id' => $fresh->id,
                'from_status' => $fromStatus,
                'to_status' => $targetStatus->value,
                'changed_by_user_id' => $actor?->id,
                'note' => 'booking.cancellation.history.'.$cancelledBy.'_cancelled',
            ]);

            $this->availability->releaseForBooking($fresh);
            $this->createRefundArtifacts($fresh, $estimate->toArray(), $reason, $details, $actor);
            $this->notifications->notifyBookingCancelled($fresh, $cancelledBy, $estimate->refundAmount);

            $cancelled = true;
        });

        return $cancelled;
    }

    private function bookingForCancellation(Booking $booking): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'reference',
                'guest_id',
                'guest_user_id',
                'host_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'subtotal',
                'subtotal_amount',
                'discount_amount',
                'cleaning_fee',
                'cleaning_fee_amount',
                'deposit',
                'deposit_amount',
                'service_fee',
                'service_fee_amount',
                'total',
                'total_amount',
                'currency',
                'cancellation_policy',
                'free_cancel_before',
            ])
            ->with(['sleepingPlace:id'])
            ->findOrFail($booking->id);
    }

    /**
     * @param  array{
     *     currency:string,
     *     paid_amount:float,
     *     refund_amount:float,
     *     non_refundable_amount:float,
     *     explanation_key:string,
     *     window:string,
     *     lines:list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool}>
     * }  $estimate
     */
    private function createRefundArtifacts(
        Booking $booking,
        array $estimate,
        ?string $reason,
        ?string $details,
        ?User $actor,
    ): void {
        if ($estimate['refund_amount'] <= 0.0) {
            return;
        }

        RefundRequest::query()->create([
            'booking_id' => $booking->id,
            'requested_by_user_id' => $actor?->id,
            'amount' => $estimate['refund_amount'],
            'currency' => $estimate['currency'],
            'reason' => $reason,
            'details' => $details ?: $estimate['explanation_key'],
            'status' => RefundRequestStatus::Requested->value,
        ]);

        PaymentRecord::query()->create([
            'booking_id' => $booking->id,
            'payer_user_id' => $booking->guest_user_id,
            'provider' => 'manual_refund_placeholder',
            'provider_reference' => 'refund-'.$booking->id.'-'.Str::lower(Str::random(8)),
            'amount' => $estimate['refund_amount'],
            'currency' => $estimate['currency'],
            'status' => $this->refundRecordStatus($estimate['paid_amount'], $estimate['refund_amount'])->value,
            'paid_at' => now(),
            'metadata_json' => [
                'reason' => $reason,
                'details' => $details,
                'estimate' => $estimate,
            ],
        ]);
    }

    private function paymentStatusAfterRefund(Booking $booking, float $paidAmount, float $refundAmount): PaymentStatus
    {
        if ($paidAmount <= 0.0) {
            return $booking->payment_status instanceof PaymentStatus
                ? $booking->payment_status
                : PaymentStatus::tryFrom((string) $booking->payment_status) ?? PaymentStatus::Unpaid;
        }

        if ($refundAmount >= $paidAmount) {
            return PaymentStatus::RefundedFull;
        }

        if ($refundAmount > 0.0) {
            return PaymentStatus::RefundedPartial;
        }

        return PaymentStatus::Paid;
    }

    private function refundRecordStatus(float $paidAmount, float $refundAmount): PaymentRecordStatus
    {
        return $refundAmount >= $paidAmount && $paidAmount > 0.0
            ? PaymentRecordStatus::RefundedFull
            : PaymentRecordStatus::RefundedPartial;
    }
}
