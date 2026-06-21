<?php

namespace App\Services\BookingRequests;

use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Bookings\StayLengthCalculatorService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingRequestCreationService
{
    public function __construct(
        private readonly BookingRequestNumberService $numbers,
        private readonly StayLengthCalculatorService $stayLength,
        private readonly BookingRequestHostViewService $hostView,
        private readonly BookingRequestWarningService $warnings,
        private readonly BookingRequestCompatibilityService $compatibility,
        private readonly BookingRequestAvailabilityHoldService $holds,
        private readonly BookingRequestNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromQuote(User $guest, BookingQuote $quote, array $data): BookingRequest
    {
        return DB::transaction(function () use ($guest, $quote, $data): BookingRequest {
            $quote = BookingQuote::query()
                ->with(['guest', 'host', 'sleepingPlace.room', 'sleepingPlace.property', 'lines'])
                ->whereKey($quote->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertUsableQuote($guest, $quote);

            $request = BookingRequest::query()->create([
                ...$this->payloadFromQuote($guest, $quote, $data),
                'request_type' => $data['request_type'] ?? $this->requestTypeForQuote($quote),
                'status' => BookingRequest::STATUS_WAITING_HOST_RESPONSE,
                'expires_at' => $data['expires_at'] ?? $this->defaultExpiry($data['request_type'] ?? $this->requestTypeForQuote($quote)),
                'hold_dates' => (bool) ($data['hold_dates'] ?? $this->defaultHoldDates($data['request_type'] ?? $this->requestTypeForQuote($quote))),
                'hold_expires_at' => $data['hold_expires_at'] ?? null,
            ]);

            $this->statusLog($request, null, BookingRequest::STATUS_WAITING_HOST_RESPONSE, $guest);
            $this->persistDecisionSupport($request);
            $this->holds->createTemporaryHold($request);
            $this->notifications->notifyHostNewRequest($request->fresh(['host', 'guest']));

            return $request->fresh(['warnings', 'compatibilityResults', 'dateLocks']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPreliminaryInquiry(User $guest, SleepingPlace $place, array $data): BookingRequest
    {
        return DB::transaction(function () use ($guest, $place, $data): BookingRequest {
            $place->loadMissing(['room', 'property', 'host']);
            $checkIn = CarbonImmutable::parse($data['check_in_date'])->startOfDay();
            $checkOut = CarbonImmutable::parse($data['check_out_date'])->startOfDay();
            $nights = $this->stayLength->calculateNights($checkIn, $checkOut);

            $request = BookingRequest::query()->create([
                'request_number' => $this->numbers->generate(),
                'guest_user_id' => $guest->id,
                'host_user_id' => $place->user_id ?: $place->property?->host_user_id,
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'sleeping_place_id' => $place->id,
                'request_type' => BookingRequest::TYPE_PRELIMINARY_INQUIRY,
                'status' => BookingRequest::STATUS_WAITING_HOST_RESPONSE,
                'hold_dates' => false,
                'expires_at' => now()->addHours(48),
                'check_in_date' => $checkIn->toDateString(),
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_date' => $checkOut->toDateString(),
                'check_out_time' => $data['check_out_time'] ?? null,
                'nights_count' => $nights,
                'chargeable_days_count' => $nights,
                'calendar_presence_days_count' => $this->stayLength->calculateCalendarPresenceDays($checkIn, $checkOut),
                'guests_count' => max(1, (int) ($data['guests_count'] ?? 1)),
                ...$this->guestDetailsPayload($data),
                'guest_profile_snapshot_json' => $this->hostView->buildGuestProfileSnapshot($guest),
                'guest_rating_snapshot_json' => $this->hostView->buildGuestRatingSnapshot($guest),
                'price_snapshot_json' => [],
                'total_amount' => 0,
                'deposit_amount' => 0,
                'cleaning_fee_amount' => 0,
                'service_fee_amount' => 0,
                'currency' => strtoupper((string) ($place->currency ?: 'EUR')),
            ]);

            $this->statusLog($request, null, BookingRequest::STATUS_WAITING_HOST_RESPONSE, $guest);
            $this->persistDecisionSupport($request);
            $this->notifications->notifyHostNewRequest($request->fresh(['host', 'guest']));

            return $request->fresh(['warnings', 'compatibilityResults', 'dateLocks']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createLongTermRequest(User $guest, BookingQuote $quote, array $data): BookingRequest
    {
        return $this->createFromQuote($guest, $quote, [
            ...$data,
            'request_type' => BookingRequest::TYPE_LONG_TERM_REQUEST,
            'expires_at' => $data['expires_at'] ?? now()->addHours(72),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSameDayUrgentRequest(User $guest, BookingQuote $quote, array $data): BookingRequest
    {
        return $this->createFromQuote($guest, $quote, [
            ...$data,
            'request_type' => BookingRequest::TYPE_SAME_DAY_URGENT,
            'hold_dates' => true,
            'expires_at' => $data['expires_at'] ?? now()->addHours(2),
            'hold_expires_at' => $data['hold_expires_at'] ?? now()->addHours(2),
        ]);
    }

    private function assertUsableQuote(User $guest, BookingQuote $quote): void
    {
        if ((int) $quote->user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'quote' => __('booking_quotes.messages.quote_not_available'),
            ]);
        }

        if ($quote->status !== BookingQuote::STATUS_VALID || $quote->expires_at?->isPast()) {
            throw ValidationException::withMessages([
                'quote' => __('booking_quotes.messages.quote_recalculate_required'),
            ]);
        }

        if (! in_array($quote->availability_status, ['available', 'request_only'], true)) {
            throw ValidationException::withMessages([
                'quote' => __('booking_dates.validation.sleeping_place_unavailable'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payloadFromQuote(User $guest, BookingQuote $quote, array $data): array
    {
        return [
            'request_number' => $this->numbers->generate(),
            'booking_quote_id' => $quote->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $quote->host_user_id,
            'property_id' => $quote->property_id,
            'room_id' => $quote->room_id,
            'sleeping_place_id' => $quote->sleeping_place_id,
            'check_in_date' => $quote->check_in_date,
            'check_in_time' => $quote->check_in_time,
            'check_out_date' => $quote->check_out_date,
            'check_out_time' => $quote->check_out_time,
            'nights_count' => $quote->nights_count,
            'chargeable_days_count' => $quote->chargeable_days_count,
            'calendar_presence_days_count' => $quote->calendar_presence_days_count,
            'guests_count' => $quote->guests_count,
            ...$this->guestDetailsPayload($data),
            'guest_profile_snapshot_json' => $this->hostView->buildGuestProfileSnapshot($guest),
            'guest_rating_snapshot_json' => $this->hostView->buildGuestRatingSnapshot($guest),
            'price_snapshot_json' => $this->priceSnapshot($quote),
            'total_amount' => $quote->total_payable,
            'deposit_amount' => $quote->deposit_amount,
            'cleaning_fee_amount' => $quote->cleaning_fee_amount,
            'service_fee_amount' => $quote->service_fee_amount,
            'currency' => $quote->currency,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function guestDetailsPayload(array $data): array
    {
        return [
            'trip_purpose' => $data['trip_purpose'] ?? null,
            'planned_arrival_time' => $data['planned_arrival_time'] ?? null,
            'planned_departure_time' => $data['planned_departure_time'] ?? null,
            'guest_message' => $data['guest_message'] ?? $data['message'] ?? null,
            'has_baggage' => (bool) ($data['has_baggage'] ?? false),
            'needs_luggage_storage' => (bool) ($data['needs_luggage_storage'] ?? false),
            'needs_early_check_in' => (bool) ($data['needs_early_check_in'] ?? false),
            'needs_late_checkout' => (bool) ($data['needs_late_checkout'] ?? false),
            'needs_residence_registration' => (bool) ($data['needs_residence_registration'] ?? false),
            'needs_reporting_documents' => (bool) ($data['needs_reporting_documents'] ?? false),
            'guest_agreed_to_rules' => (bool) ($data['guest_agreed_to_rules'] ?? false),
            'guest_agreed_to_cancellation_policy' => (bool) ($data['guest_agreed_to_cancellation_policy'] ?? false),
            'guest_agreed_to_deposit_policy' => (bool) ($data['guest_agreed_to_deposit_policy'] ?? false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function priceSnapshot(BookingQuote $quote): array
    {
        $quote->loadMissing('lines');

        return [
            'quote_number' => $quote->quote_number,
            'accommodation_amount' => (float) $quote->accommodation_amount,
            'discount_amount' => (float) $quote->discount_amount,
            'cleaning_fee_amount' => (float) $quote->cleaning_fee_amount,
            'service_fee_amount' => (float) $quote->service_fee_amount,
            'deposit_amount' => (float) $quote->deposit_amount,
            'total_without_deposit' => (float) $quote->total_without_deposit,
            'total_payable' => (float) $quote->total_payable,
            'currency' => $quote->currency,
            'lines' => $quote->lines
                ->map(fn ($line): array => [
                    'line_type' => $line->line_type,
                    'label_key' => $line->label_key,
                    'date' => $line->date?->toDateString(),
                    'amount' => (float) $line->amount,
                    'currency' => $line->currency,
                    'is_refundable' => (bool) $line->is_refundable,
                ])
                ->values()
                ->all(),
        ];
    }

    private function persistDecisionSupport(BookingRequest $request): void
    {
        $compatibility = $this->compatibility->createCompatibilityResults($request);
        $warnings = $this->warnings->generateWarnings($request);

        $request->forceFill([
            'compatibility_snapshot_json' => $compatibility->map->only(['compatibility_key', 'status', 'severity', 'message_key', 'message_params_json'])->values()->all(),
            'warnings_snapshot_json' => $warnings->map->only(['warning_key', 'severity', 'message_key', 'message_params_json', 'blocking'])->values()->all(),
        ])->save();
    }

    private function requestTypeForQuote(BookingQuote $quote): string
    {
        return $quote->availability_status === 'request_only'
            ? BookingRequest::TYPE_REQUEST_ONLY
            : BookingRequest::TYPE_HOST_APPROVAL;
    }

    private function defaultExpiry(string $requestType): CarbonImmutable
    {
        return match ($requestType) {
            BookingRequest::TYPE_SAME_DAY_URGENT => CarbonImmutable::now()->addHours(2),
            BookingRequest::TYPE_LONG_TERM_REQUEST => CarbonImmutable::now()->addHours(72),
            BookingRequest::TYPE_PRELIMINARY_INQUIRY => CarbonImmutable::now()->addHours(48),
            default => CarbonImmutable::now()->addDay(),
        };
    }

    private function defaultHoldDates(string $requestType): bool
    {
        return in_array($requestType, [
            BookingRequest::TYPE_HOST_APPROVAL,
            BookingRequest::TYPE_STAY_REQUEST,
            BookingRequest::TYPE_SAME_DAY_URGENT,
        ], true);
    }

    private function statusLog(BookingRequest $request, ?string $oldStatus, string $newStatus, ?User $user = null): void
    {
        $request->statusLogs()->create([
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => 'booking_requests.status_changed',
        ]);
    }
}
