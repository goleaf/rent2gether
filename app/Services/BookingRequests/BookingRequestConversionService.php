<?php

namespace App\Services\BookingRequests;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Services\Bookings\BookingPriceQuoteService;
use App\Services\Bookings\BookingQuoteConversionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingRequestConversionService
{
    public function __construct(
        private readonly BookingPriceQuoteService $priceQuotes,
        private readonly BookingQuoteConversionService $quoteConversion,
        private readonly BookingRequestAvailabilityHoldService $holds,
    ) {}

    public function convertApprovedRequestToBooking(BookingRequest $request): Booking
    {
        return $this->createBookingAfterApproval($request);
    }

    public function createBookingAfterApproval(BookingRequest $request): Booking
    {
        return DB::transaction(function () use ($request): Booking {
            $request = BookingRequest::query()
                ->with(['guest', 'bookingQuote', 'dateLocks'])
                ->whereKey($request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== BookingRequest::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'booking_request' => __('booking_requests.validation.request_must_be_approved'),
                ]);
            }

            $quote = $request->bookingQuote;

            if (! $quote) {
                throw ValidationException::withMessages([
                    'booking_quote_id' => __('booking_quotes.messages.quote_not_available'),
                ]);
            }

            $this->holds->releaseHold($request, 'converting_to_booking');
            $quote = $this->priceQuotes->recalculateQuote($quote);
            $booking = $this->quoteConversion->convertToBooking($request->guest, $quote);

            $this->copyRequestDataToBooking($request, $booking);
            $request->forceFill([
                'booking_id' => $booking->id,
                'status' => BookingRequest::STATUS_CONVERTED_TO_BOOKING,
                'converted_to_booking_at' => now(),
            ])->save();

            $request->statusLogs()->create([
                'user_id' => $request->host_user_id,
                'old_status' => BookingRequest::STATUS_APPROVED,
                'new_status' => BookingRequest::STATUS_CONVERTED_TO_BOOKING,
                'reason_key' => 'booking_requests.converted_to_booking',
            ]);

            return $booking->fresh(['priceLines', 'priceSnapshot', 'sleepingPlaceDateLocks']);
        });
    }

    public function createPaymentDeadline(BookingRequest $request, Booking $booking): void
    {
        $booking->forceFill([
            'payment_deadline_at' => $request->bookingQuote?->payment_deadline_at ?: now()->addMinutes(20),
            'availability_hold_expires_at' => $request->bookingQuote?->payment_deadline_at ?: now()->addMinutes(20),
        ])->save();
    }

    public function copyRequestDataToBooking(BookingRequest $request, Booking $booking): void
    {
        $booking->forceFill([
            'guest_message' => $request->guest_message,
            'guests_count' => $request->guests_count,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
        ])->save();
    }
}
