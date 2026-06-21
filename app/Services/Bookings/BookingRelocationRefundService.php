<?php

namespace App\Services\Bookings;

use App\Models\BookingRefund;
use App\Models\BookingRelocation;

class BookingRelocationRefundService
{
    public function __construct(
        private readonly BookingPaymentNumberService $numbers,
        private readonly BookingRelocationEventService $events,
        private readonly BookingRelocationNotificationService $notifications,
    ) {}

    public function createRefundIfNeeded(BookingRelocation $relocation): ?BookingRefund
    {
        if (! $relocation->requires_refund || (float) $relocation->refund_amount <= 0) {
            return null;
        }

        if ($relocation->booking_refund_id) {
            return BookingRefund::query()->find($relocation->booking_refund_id);
        }

        $refund = BookingRefund::query()->create([
            'refund_number' => $this->numbers->generateRefundNumber(),
            'booking_id' => $relocation->original_booking_id,
            'booking_payment_id' => $relocation->booking_payment_id,
            'guest_user_id' => $relocation->guest_user_id,
            'host_user_id' => $relocation->host_user_id,
            'property_id' => $relocation->current_property_id,
            'room_id' => $relocation->current_room_id,
            'sleeping_place_id' => $relocation->current_sleeping_place_id,
            'refund_type' => 'relocation_refund',
            'status' => 'pending',
            'amount' => $relocation->refund_amount,
            'currency' => $relocation->currency,
            'reason_key' => 'booking_relocations.refund.price_difference',
            'source_type' => 'booking_relocation',
            'source_id' => $relocation->id,
            'requested_at' => now(),
        ]);

        $relocation->forceFill([
            'booking_refund_id' => $refund->id,
            'refund_status' => 'pending',
            'status' => 'waiting_refund',
        ])->save();

        $this->events->record($relocation->refresh(), 'refund_created');
        $this->notifications->notifyRefundCreated($relocation->refresh());

        return $refund;
    }

    public function markRefundApproved(BookingRelocation $relocation): BookingRelocation
    {
        $this->createRefundIfNeeded($relocation)?->forceFill([
            'status' => 'approved',
            'approved_at' => now(),
        ])->save();

        $relocation->forceFill(['refund_status' => 'approved'])->save();

        return $relocation->refresh();
    }

    public function markRefundCompleted(BookingRelocation $relocation): BookingRelocation
    {
        $this->createRefundIfNeeded($relocation)?->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();

        $relocation->forceFill(['refund_status' => 'completed'])->save();

        return $relocation->refresh();
    }
}
