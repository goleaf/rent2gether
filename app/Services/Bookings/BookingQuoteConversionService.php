<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingPriceLine;
use App\Models\BookingQuote;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Availability\SleepingPlaceDateLockService;
use App\Services\Pricing\BookingPriceSnapshotService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingQuoteConversionService
{
    public function __construct(
        private readonly BookingPriceQuoteService $priceQuotes,
        private readonly BookingQuoteExpirationService $expiration,
        private readonly BookingQuoteAvailabilityService $availability,
        private readonly SleepingPlaceDateLockService $dateLocks,
        private readonly BookingTimelineDateService $timelineDates,
        private readonly AvailabilityService $legacyAvailability,
        private readonly BookingPriceSnapshotService $priceSnapshots,
    ) {}

    public function convertToBooking(User $guest, BookingQuote $quote): Booking
    {
        return DB::transaction(function () use ($guest, $quote): Booking {
            $quote = $this->lockedQuote($quote);
            $this->authorizeGuest($guest, $quote);

            if (! $this->ensureQuoteStillValid($quote)) {
                throw $this->quoteUnavailableException();
            }

            $quote = $this->priceQuotes->recalculateQuote($quote);

            if (! $this->recheckAvailability($quote) || $quote->availability_status === 'request_only') {
                throw $this->quoteUnavailableException();
            }

            $this->lockDatesForBookingAttempt($quote);

            $booking = Booking::query()->create([
                ...$this->bookingPayload($guest, $quote),
                'booking_type' => BookingType::Instant,
                'status' => BookingStatus::AwaitingPayment,
                'payment_status' => PaymentStatus::AwaitingPayment,
                'availability_hold_expires_at' => $quote->payment_deadline_at,
            ]);

            $this->dateLocks->convertQuoteLocksToBooking($quote, $booking);
            $this->legacyAvailability->blockForBooking($booking);
            $this->createSnapshots($booking, $quote);

            $quote->forceFill([
                'status' => BookingQuote::STATUS_CONVERTED_TO_BOOKING,
            ])->save();

            return $booking->load(['priceLines', 'priceSnapshot', 'timelineDates', 'sleepingPlaceDateLocks']);
        });
    }

    /**
     * Existing project convention represents stay requests as Bookings awaiting host approval.
     *
     * @param  array<string, mixed>  $requestData
     */
    public function convertToBookingRequest(User $guest, BookingQuote $quote, array $requestData): Booking
    {
        return DB::transaction(function () use ($guest, $quote, $requestData): Booking {
            $quote = $this->lockedQuote($quote);
            $this->authorizeGuest($guest, $quote);

            if (! $this->ensureQuoteStillValid($quote)) {
                throw $this->quoteUnavailableException();
            }

            $quote = $this->priceQuotes->recalculateQuote($quote);

            if (! $this->recheckAvailability($quote)) {
                throw $this->quoteUnavailableException();
            }

            $booking = Booking::query()->create([
                ...$this->bookingPayload($guest, $quote),
                'booking_type' => BookingType::HostApproval,
                'status' => BookingStatus::AwaitingHostApproval,
                'payment_status' => PaymentStatus::Unpaid,
                'availability_hold_expires_at' => CarbonImmutable::now()->addDay(),
                'guest_message' => $requestData['guest_message'] ?? $requestData['message'] ?? null,
            ]);

            $this->dateLocks->createLocksForBooking($booking, 'host_confirmation_pending');
            $this->createSnapshots($booking, $quote);

            $quote->forceFill([
                'status' => BookingQuote::STATUS_CONVERTED_TO_REQUEST,
            ])->save();

            return $booking->load(['priceLines', 'priceSnapshot', 'timelineDates', 'sleepingPlaceDateLocks']);
        });
    }

    public function ensureQuoteStillValid(BookingQuote $quote): bool
    {
        return $quote->status === BookingQuote::STATUS_VALID
            && ! $this->expiration->isExpired($quote)
            && $quote->validation_status !== 'invalid'
            && $quote->pricing_status === 'calculated';
    }

    public function recheckAvailability(BookingQuote $quote): bool
    {
        $quote = $this->availability->checkAvailability($quote);

        return in_array($quote->availability_status, ['available', 'request_only'], true)
            && $quote->validation_status !== 'invalid';
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function lockDatesForBookingAttempt(BookingQuote $quote): Collection
    {
        return $this->dateLocks->createLocksForQuote($quote);
    }

    public function createSnapshots(Booking $booking, BookingQuote $quote): void
    {
        $quote->loadMissing(['lines', 'timelineDates']);

        $quote->lines->each(function ($line) use ($booking, $quote): BookingPriceLine {
            return $booking->priceLines()->create([
                'type' => $line->line_type,
                'label_key' => $line->label_key,
                'amount' => $line->amount,
                'currency' => $line->currency,
                'is_refundable' => $line->is_refundable,
                'metadata_json' => [
                    'booking_quote_id' => $quote->id,
                    'quote_line_id' => $line->id,
                    'date' => $line->date?->toDateString(),
                    'quantity' => (float) $line->quantity,
                    'unit_amount' => (float) $line->unit_amount,
                    'is_discount' => (bool) $line->is_discount,
                    'is_fee' => (bool) $line->is_fee,
                    'is_deposit' => (bool) $line->is_deposit,
                    'is_payable_now' => (bool) $line->is_payable_now,
                ],
            ]);
        });

        $this->timelineDates->copyToBooking($quote, $booking);
        $this->priceSnapshots->createFromQuote($booking, $quote);

        $booking->statusHistories()->create([
            'from_status' => null,
            'to_status' => $this->enumValue($booking->status),
            'changed_by_user_id' => $booking->guest_user_id,
            'note' => 'booking_quotes.timeline.created_from_quote',
        ]);
    }

    private function lockedQuote(BookingQuote $quote): BookingQuote
    {
        return BookingQuote::query()
            ->with(['sleepingPlace.property.host.hostProfile', 'lines', 'timelineDates'])
            ->whereKey($quote->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function authorizeGuest(User $guest, BookingQuote $quote): void
    {
        if ((int) $quote->user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'quote' => __('booking_quotes.messages.quote_not_available'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPayload(User $guest, BookingQuote $quote): array
    {
        $place = $quote->sleepingPlace;
        $host = $quote->host_user_id;
        $nights = max(1, (int) $quote->nights_count);
        $subtotal = $this->money((float) $quote->accommodation_amount - (float) $quote->discount_amount);

        return [
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host,
            'host_user_id' => $host,
            'property_id' => $quote->property_id,
            'room_id' => $quote->room_id,
            'sleeping_place_id' => $quote->sleeping_place_id,
            'check_in' => $quote->check_in_date,
            'check_out' => $quote->check_out_date,
            'check_in_date' => $quote->check_in_date,
            'check_out_date' => $quote->check_out_date,
            'check_in_time' => $quote->check_in_time,
            'check_out_time' => $quote->check_out_time,
            'guests_count' => $quote->guests_count,
            'nights' => $quote->nights_count,
            'nights_count' => $quote->nights_count,
            'calendar_days_count' => $quote->calendar_presence_days_count,
            'price_per_night' => $this->money((float) $quote->accommodation_amount / $nights),
            'subtotal' => $subtotal,
            'subtotal_amount' => $subtotal,
            'discount_amount' => $quote->discount_amount,
            'cleaning_fee' => $quote->cleaning_fee_amount,
            'cleaning_fee_amount' => $quote->cleaning_fee_amount,
            'deposit' => $quote->deposit_amount,
            'deposit_amount' => $quote->deposit_amount,
            'service_fee' => $quote->service_fee_amount,
            'service_fee_amount' => $quote->service_fee_amount,
            'tax_amount' => $quote->tax_amount,
            'city_fee_amount' => $quote->city_fee_amount,
            'total' => $quote->total_payable,
            'total_amount' => $quote->total_payable,
            'refundable_amount' => $quote->refundable_amount,
            'non_refundable_amount' => $quote->non_refundable_amount,
            'currency' => $quote->currency,
            'payment_deadline_at' => $quote->payment_deadline_at,
            'requires_document_check' => false,
            'requires_phone_check' => false,
            'requires_identity_check' => false,
            'cancellation_policy' => $this->cancellationPolicy($place?->cancellation_policy),
            'refund_status' => 'none',
            'guest_message' => $quote->check_in_comment,
            'free_cancel_before' => $quote->free_cancellation_until,
            'has_dispute' => false,
            'has_complaint' => false,
            'guest_review_left' => false,
            'host_review_left' => false,
        ];
    }

    private function cancellationPolicy(mixed $policy): CancellationPolicy
    {
        if ($policy instanceof CancellationPolicy) {
            return $policy;
        }

        return is_string($policy)
            ? (CancellationPolicy::tryFrom($policy) ?? CancellationPolicy::Flexible)
            : CancellationPolicy::Flexible;
    }

    private function quoteUnavailableException(): ValidationException
    {
        return ValidationException::withMessages([
            'quote' => __('booking_quotes.messages.quote_recalculate_required'),
        ]);
    }

    private function enumValue(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }
}
