<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingPayment;
use App\Models\BookingRequest;
use Illuminate\Support\Facades\DB;

class BookingPaymentCreationService
{
    public function __construct(
        private readonly BookingPaymentNumberService $numbers,
        private readonly BookingPaymentAllocationService $allocations,
        private readonly BookingPaymentDeadlineService $deadlines,
        private readonly BookingPaymentStateService $states,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function createForBooking(Booking $booking, array $options = []): BookingPayment
    {
        return DB::transaction(function () use ($booking, $options): BookingPayment {
            $booking->loadMissing('bookingQuote', 'sourceBookingRequest');

            $amount = (float) ($options['amount'] ?? $booking->total_payable);
            $requiredNow = (float) ($options['required_now_amount'] ?? $amount);
            $remaining = (float) ($options['remaining_amount'] ?? 0);
            $deadline = $options['payment_deadline_at'] ?? $booking->payment_deadline_at ?? now()->addMinutes(30);

            $payment = BookingPayment::query()->create([
                'payment_number' => $this->numbers->generatePaymentNumber(),
                'booking_id' => $booking->id,
                'booking_quote_id' => $booking->booking_quote_id,
                'booking_request_id' => $booking->booking_request_id,
                'booking_extension_id' => $options['booking_extension_id'] ?? null,
                'booking_relocation_id' => $options['booking_relocation_id'] ?? null,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'payment_type' => $options['payment_type'] ?? $booking->payment_type ?? 'full_payment',
                'payment_purpose' => $options['payment_purpose'] ?? 'booking_payment',
                'payment_method' => $options['payment_method'] ?? 'internal_test',
                'status' => 'waiting_payment',
                'amount' => $amount,
                'currency' => $booking->currency,
                'required_now_amount' => $requiredNow,
                'remaining_amount' => $remaining,
                'remaining_due_at' => $options['remaining_due_at'] ?? null,
                'payment_deadline_at' => $deadline,
                'description' => $options['description'] ?? null,
            ]);

            $this->allocations->createAllocationsFromBooking($payment, $booking);
            $this->deadlines->createDeadline($payment, $payment->payment_deadline_at);

            $booking->forceFill([
                'payment_status' => 'waiting_payment',
                'payment_deadline_at' => $payment->payment_deadline_at,
                'availability_hold_expires_at' => $booking->availability_hold_expires_at ?: $payment->payment_deadline_at,
            ])->save();

            $this->states->markWaitingPayment($payment);

            return $payment->fresh(['allocations', 'deadlines']);
        });
    }

    public function createForApprovedRequest(BookingRequest $request, Booking $booking): BookingPayment
    {
        return $this->createForBooking($booking, [
            'payment_purpose' => 'booking_payment',
            'description' => 'booking_request:'.$request->id,
        ]);
    }

    public function createForExtension(BookingExtension $extension): BookingPayment
    {
        return $this->createForBooking($extension->booking, [
            'booking_extension_id' => $extension->id,
            'payment_type' => 'extension_payment',
            'payment_purpose' => 'extension_payment',
            'amount' => $extension->additional_amount ?? $extension->extension_amount_due ?? 0,
            'required_now_amount' => $extension->additional_amount ?? $extension->extension_amount_due ?? 0,
            'payment_deadline_at' => $extension->payment_deadline_at ?? now()->addMinutes(30),
        ]);
    }

    public function createForRelocation(object $relocation): BookingPayment
    {
        return $this->createForBooking($relocation->booking, [
            'booking_relocation_id' => $relocation->id ?? null,
            'payment_type' => 'relocation_difference',
            'payment_purpose' => 'relocation_payment',
            'amount' => $relocation->price_difference_amount ?? 0,
            'required_now_amount' => $relocation->price_difference_amount ?? 0,
        ]);
    }

    public function createRemainingBalancePayment(Booking $booking): BookingPayment
    {
        $remaining = (float) $booking->bookingPayments()
            ->where('remaining_amount', '>', 0)
            ->orderByDesc('id')
            ->value('remaining_amount');

        if ($remaining <= 0) {
            $remaining = max(0, (float) $booking->total_payable - app(BookingPaymentService::class)->getPaidAmount($booking));
        }

        return $this->createForBooking($booking, [
            'payment_type' => 'remaining_balance',
            'payment_purpose' => 'booking_payment',
            'amount' => $remaining,
            'required_now_amount' => $remaining,
            'remaining_amount' => 0,
        ]);
    }
}
