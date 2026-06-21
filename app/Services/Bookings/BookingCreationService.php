<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestConversionService;
use Illuminate\Support\Facades\DB;

class BookingCreationService
{
    public function __construct(
        private readonly BookingQuoteConversionService $quoteConversion,
        private readonly BookingRequestConversionService $requestConversion,
        private readonly BookingNumberService $numbers,
        private readonly BookingRequirementService $requirements,
        private readonly BookingSnapshotService $snapshots,
        private readonly BookingLifecycleEventService $events,
        private readonly BookingNotificationIntegrationService $notifications,
        private readonly BookingMessageIntegrationService $messages,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromQuote(User $guest, BookingQuote $quote, array $data = []): Booking
    {
        return $this->createInstantBooking($guest, $quote, $data);
    }

    public function createFromApprovedRequest(BookingRequest $request): Booking
    {
        return DB::transaction(function () use ($request): Booking {
            $booking = $this->requestConversion->convertApprovedRequestToBooking($request);
            $request = $request->fresh(['bookingQuote']);

            return $this->decorateBooking($booking, $request?->bookingQuote, $request, [
                'booking_type' => BookingType::HostApproval->value,
                'approval_type' => 'requires_host_confirmation',
                'status' => BookingStatus::WaitingPayment->value,
                'payment_status' => PaymentStatus::WaitingPayment->value,
                'source_type' => 'booking_request',
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createInstantBooking(User $guest, BookingQuote $quote, array $data = []): Booking
    {
        return DB::transaction(function () use ($guest, $quote, $data): Booking {
            $booking = $this->quoteConversion->convertToBooking($guest, $quote);

            return $this->decorateBooking($booking, $quote->fresh(['lines', 'timelineDates']), null, [
                'booking_type' => BookingType::Instant->value,
                'approval_type' => 'instant_confirmed',
                'status' => BookingStatus::WaitingPayment->value,
                'payment_status' => PaymentStatus::WaitingPayment->value,
                'source_type' => 'booking_quote',
                ...$data,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createHostApprovalBooking(User $guest, BookingQuote $quote, array $data = []): Booking
    {
        return DB::transaction(function () use ($guest, $quote, $data): Booking {
            $booking = $this->quoteConversion->convertToBookingRequest($guest, $quote, $data);

            return $this->decorateBooking($booking, $quote->fresh(['lines', 'timelineDates']), null, [
                'booking_type' => BookingType::HostApproval->value,
                'approval_type' => 'requires_host_confirmation',
                'status' => BookingStatus::WaitingHostConfirmation->value,
                'payment_status' => PaymentStatus::Unpaid->value,
                'source_type' => 'booking_quote',
                ...$data,
            ]);
        });
    }

    public function createStayRequestBooking(BookingRequest $request): Booking
    {
        return $this->createFromApprovedRequest($request);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSameDayUrgentBooking(User $guest, BookingQuote $quote, array $data = []): Booking
    {
        return $this->createInstantBooking($guest, $quote, [
            'booking_type' => BookingType::SameDayUrgent->value,
            'payment_deadline_at' => now()->addHours(2),
            'availability_hold_expires_at' => now()->addHours(2),
            ...$data,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function decorateBooking(Booking $booking, ?BookingQuote $quote, ?BookingRequest $request, array $data): Booking
    {
        $quote?->loadMissing('lines');
        $booking->loadMissing(['guest', 'priceLines']);

        $depositMode = (string) ($data['deposit_mode'] ?? ((float) $booking->deposit_amount > 0 ? 'with_deposit' : 'without_deposit'));

        $payload = [
            'booking_number' => $booking->booking_number ?: $this->numbers->generate(),
            'booking_quote_id' => $quote?->id ?: $booking->booking_quote_id,
            'booking_request_id' => $request?->id ?: $booking->booking_request_id,
            'booking_type' => $data['booking_type'] ?? $this->value($booking->booking_type),
            'approval_type' => $data['approval_type'] ?? ($booking->approval_type ?: 'instant_confirmed'),
            'payment_type' => $data['payment_type'] ?? ($booking->payment_type ?: 'full_payment'),
            'deposit_mode' => $depositMode,
            'guest_group_type' => $data['guest_group_type'] ?? $this->guestGroupType($booking),
            'source_type' => $data['source_type'] ?? $booking->source_type,
            'status' => $data['status'] ?? $this->value($booking->status),
            'payment_status' => $data['payment_status'] ?? $this->value($booking->payment_status),
            'chargeable_days_count' => $quote?->chargeable_days_count ?: $booking->chargeable_days_count ?: $booking->nights_count,
            'calendar_presence_days_count' => $quote?->calendar_presence_days_count ?: $booking->calendar_presence_days_count ?: $booking->calendar_days_count,
            'included_guests_count' => $quote?->included_guests_count ?: $booking->included_guests_count ?: 1,
            'extra_guests_count' => $quote?->extra_guests_count ?: max(0, (int) $booking->guests_count - 1),
            'accommodation_amount' => $quote?->accommodation_amount ?: $booking->accommodation_amount ?: $booking->subtotal_amount,
            'total_without_deposit' => $quote?->total_without_deposit ?: $booking->total_without_deposit,
            'total_payable' => $quote?->total_payable ?: $booking->total_payable ?: $booking->total_amount,
            'host_payout_amount' => $quote?->host_payout_preview_amount ?: $booking->host_payout_amount,
            'nightly_price_snapshot' => $this->nightlySnapshot($booking, $quote),
            'payment_deadline_at' => $data['payment_deadline_at'] ?? $booking->payment_deadline_at,
            'availability_hold_expires_at' => $data['availability_hold_expires_at'] ?? $booking->availability_hold_expires_at,
            'requires_phone_verification' => (bool) ($data['requires_phone_verification'] ?? $booking->requires_phone_verification),
            'requires_identity_verification' => (bool) ($data['requires_identity_verification'] ?? $booking->requires_identity_verification),
            'requires_document_verification' => (bool) ($data['requires_document_verification'] ?? $booking->requires_document_verification),
        ];

        if (($data['guest_agreed_to_rules'] ?? false) && $booking->rules_accepted_at === null) {
            $payload['rules_accepted_at'] = now();
        }

        $payload['verification_status'] = $this->verificationStatus($payload);

        $booking->forceFill($payload)->save();

        $booking = $booking->fresh(['guest', 'bookingGuests']);
        $this->ensureBookingGuests($booking, $data);
        $this->requirements->createRequirements($booking->fresh());
        $this->snapshots->createAllSnapshots($booking->fresh(), $quote);
        $this->recordCreatedLifecycle($booking->fresh());
        $this->notifications->notifyBookingCreated($booking->fresh());
        $this->messages->createOrLinkBookingThread($booking->fresh());

        return $booking->fresh([
            'bookingGuests',
            'requirements',
            'statusLogs',
            'lifecycleEvents',
            'priceLines',
            'priceSnapshot',
            'sleepingPlaceDateLocks',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureBookingGuests(Booking $booking, array $data): void
    {
        if (! $booking->bookingGuests()->where('is_main_guest', true)->exists()) {
            $guestName = $booking->guest?->name ?: 'Guest '.$booking->guest_user_id;

            $booking->bookingGuests()->create([
                'user_id' => $booking->guest_user_id,
                'guest_name' => $guestName,
                'guest_email' => $booking->guest?->email,
                'guest_type' => 'main_guest',
                'verification_status' => $booking->verification_status,
                'is_main_guest' => true,
                'full_name' => $guestName,
            ]);
        }

        if ((int) $booking->guests_count > 1 && ! $booking->bookingGuests()->where('guest_type', 'second_guest')->exists()) {
            $guestName = (string) ($data['second_guest_name'] ?? __('bookings.fields.second_guest'));

            $booking->bookingGuests()->create([
                'user_id' => null,
                'guest_name' => $guestName,
                'guest_email' => $data['second_guest_email'] ?? null,
                'guest_phone' => $data['second_guest_phone'] ?? null,
                'guest_type' => 'second_guest',
                'verification_status' => 'not_required',
                'is_main_guest' => false,
                'full_name' => $guestName,
            ]);
        }
    }

    private function recordCreatedLifecycle(Booking $booking): void
    {
        if (! $booking->statusLogs()->exists()) {
            $booking->statusLogs()->create([
                'user_id' => $booking->guest_user_id,
                'old_status' => null,
                'new_status' => $this->value($booking->status),
                'reason_key' => 'bookings.lifecycle_events.created',
            ]);
        }

        if (! $booking->lifecycleEvents()->where('event_key', 'created')->exists()) {
            $this->events->record($booking, 'created', [
                'event_type' => 'system',
                'user_id' => $booking->guest_user_id,
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function nightlySnapshot(Booking $booking, ?BookingQuote $quote): array
    {
        $lines = $quote?->lines ?: $booking->priceLines;

        return $lines
            ->filter(fn ($line): bool => in_array((string) ($line->line_type ?? $line->type), ['night', 'weekday_night', 'weekend_night', 'holiday_night', 'date_override'], true))
            ->map(fn ($line): array => [
                'date' => $line->date?->toDateString() ?? data_get($line->metadata_json, 'date'),
                'line_type' => (string) ($line->line_type ?? $line->type),
                'label_key' => (string) $line->label_key,
                'amount' => (float) $line->amount,
                'currency' => (string) $line->currency,
            ])
            ->values()
            ->all();
    }

    private function guestGroupType(Booking $booking): string
    {
        if ((int) $booking->guests_count > 1) {
            return 'two_guests_one_double_place';
        }

        return 'single_guest';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function verificationStatus(array $payload): string
    {
        return ($payload['requires_phone_verification'] || $payload['requires_identity_verification'] || $payload['requires_document_verification'])
            ? 'pending'
            : 'not_required';
    }

    private function value(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }
}
