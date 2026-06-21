<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Pricing\BookingPriceQuoteEngine;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BookingPriceQuoteService
{
    public function __construct(
        private readonly BookingQuoteNumberService $numbers,
        private readonly StayLengthCalculatorService $stayLength,
        private readonly BookingDateValidationService $validation,
        private readonly BookingQuoteAvailabilityService $availability,
        private readonly BookingPriceQuoteEngine $priceEngine,
        private readonly BookingCancellationDateService $cancellationDates,
        private readonly BookingTimelineDateService $timelineDates,
        private readonly BookingQuoteSuggestionService $suggestions,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createQuote(User $guest, SleepingPlace $place, array $data): BookingQuote
    {
        return DB::transaction(function () use ($guest, $place, $data): BookingQuote {
            $place = $this->loadPlace($place);
            $payload = $this->basePayload($guest, $place, $data);
            $quote = BookingQuote::query()->create($payload);

            return $this->calculateAndPersist($guest, $quote);
        });
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function recalculateQuote(BookingQuote $quote, array $changes = []): BookingQuote
    {
        return DB::transaction(function () use ($quote, $changes): BookingQuote {
            $quote->forceFill($this->recalculationPayload($quote, $changes))->save();
            $quote->lines()->delete();
            $quote->validationResults()->delete();
            $quote->timelineDates()->delete();
            $quote->suggestions()->delete();

            $quote->loadMissing(['guest', 'sleepingPlace']);

            return $this->calculateAndPersist($quote->guest, $quote);
        });
    }

    public function calculateAccommodationAmount(BookingQuote $quote): float
    {
        return $this->money($quote->accommodation_amount);
    }

    public function calculateDiscounts(BookingQuote $quote): float
    {
        return $this->money($quote->discount_amount);
    }

    public function calculateCleaningFee(BookingQuote $quote): float
    {
        return $this->money($quote->cleaning_fee_amount);
    }

    public function calculateServiceFee(BookingQuote $quote): float
    {
        return $this->money($quote->service_fee_amount);
    }

    public function calculateDeposit(BookingQuote $quote): float
    {
        return $this->money($quote->deposit_amount);
    }

    public function calculateTaxes(BookingQuote $quote): float
    {
        return $this->money((float) $quote->tax_amount + (float) $quote->city_fee_amount);
    }

    public function calculateHostPayoutPreview(BookingQuote $quote): float
    {
        return $this->money($quote->host_payout_preview_amount);
    }

    public function calculateRefundableAmount(BookingQuote $quote): float
    {
        return $this->money($quote->refundable_amount);
    }

    public function calculateTotal(BookingQuote $quote): float
    {
        return $this->money($quote->total_payable);
    }

    private function calculateAndPersist(User $guest, BookingQuote $quote): BookingQuote
    {
        $quote->loadMissing(['sleepingPlace.room', 'sleepingPlace.property', 'guest']);
        $place = $this->loadPlace($quote->sleepingPlace);
        $validationResults = $this->validation->validateDates($guest, $place, $quote->only([
            'rental_mode',
            'check_in_date',
            'check_out_date',
            'guests_count',
            'requires_identity_verification',
        ]));

        $quote->validationResults()->createMany($validationResults->map(fn (array $result): array => $result)->all());

        $hasBlocking = $validationResults->contains(fn (array $result): bool => $result['blocking']);
        $hasWarnings = $validationResults->isNotEmpty();

        if ($hasBlocking) {
            $this->availability->markUnavailable($quote, $validationResults->pluck('validation_key'));
            $quote->forceFill([
                'status' => BookingQuote::STATUS_INVALID,
                'validation_status' => 'invalid',
                'pricing_status' => 'failed',
            ])->save();
            $this->suggestions->createSuggestionsForInvalidQuote($quote);

            return $quote->fresh(['lines', 'validationResults', 'timelineDates', 'suggestions']);
        }

        $this->availability->checkAvailability($quote);
        $quote = $this->priceEngine->recalculateExistingQuote($quote);

        if ($quote->status === BookingQuote::STATUS_INVALID) {
            $this->suggestions->createSuggestionsForInvalidQuote($quote);

            return $quote->fresh(['lines', 'validationResults', 'timelineDates', 'suggestions']);
        }

        $cancellation = $this->cancellationDates->buildCancellationPreview($quote);
        $paymentDeadline = CarbonImmutable::now()->addMinutes(20);
        $checkIn = CarbonImmutable::instance($quote->check_in_date);
        $checkOut = CarbonImmutable::instance($quote->check_out_date);
        $hasPricingOrDateWarnings = $quote->validationResults()->where('blocking', false)->exists();

        $quote->forceFill([
            'availability_status' => $quote->availability_status === 'request_only' ? 'request_only' : 'available',
            'validation_status' => ($hasWarnings || $hasPricingOrDateWarnings) ? 'warnings' : 'valid',
            'pricing_status' => 'calculated',
            'free_cancellation_until' => $cancellation['free_cancellation_until'],
            'cancellation_penalty_starts_at' => $cancellation['cancellation_penalty_starts_at'],
            'payment_deadline_at' => $paymentDeadline,
            'host_payout_due_at' => $checkOut->addDay()->setTime(12, 0),
            'guest_check_in_reminder_at' => $checkIn->subDay()->setTime(18, 0),
            'guest_check_out_reminder_at' => $checkOut->subDay()->setTime(18, 0),
            'host_check_in_reminder_at' => $checkIn->subDay()->setTime(18, 0),
            'host_check_out_reminder_at' => $checkOut->subDay()->setTime(18, 0),
            'deposit_review_start_at' => $checkOut->setTime(12, 0),
            'review_request_at' => $checkOut->addDay()->setTime(18, 0),
            'expires_at' => $paymentDeadline,
            'status' => BookingQuote::STATUS_VALID,
        ])->save();

        $this->timelineDates->buildForQuote($quote);

        return $quote->fresh(['lines', 'validationResults', 'timelineDates', 'suggestions']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function basePayload(User $guest, SleepingPlace $place, array $data): array
    {
        $checkIn = CarbonImmutable::parse($data['check_in_date'])->startOfDay();
        $checkOut = CarbonImmutable::parse($data['check_out_date'])->startOfDay();
        $nights = $this->stayLength->calculateNights($checkIn, $checkOut);

        return [
            'quote_number' => $this->numbers->generate(),
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'room_id' => $place->room_id,
            'property_id' => $place->property_id,
            'host_user_id' => $place->user_id ?: $place->property?->host_user_id,
            'rental_mode' => (string) ($data['rental_mode'] ?? 'nightly'),
            'check_in_date' => $checkIn->toDateString(),
            'check_in_time' => $data['check_in_time'] ?? null,
            'check_out_date' => $checkOut->toDateString(),
            'check_out_time' => $data['check_out_time'] ?? null,
            'check_in_window' => $data['check_in_window'] ?? null,
            'check_out_window' => $data['check_out_window'] ?? null,
            'nights_count' => $nights,
            'chargeable_days_count' => $nights,
            'calendar_presence_days_count' => $this->stayLength->calculateCalendarPresenceDays($checkIn, $checkOut),
            'guests_count' => max(1, (int) ($data['guests_count'] ?? 1)),
            'included_guests_count' => 1,
            'extra_guests_count' => max(0, (int) ($data['guests_count'] ?? 1) - 1),
            'early_check_in_requested' => (bool) ($data['early_check_in_requested'] ?? false),
            'late_check_out_requested' => (bool) ($data['late_check_out_requested'] ?? false),
            'flexible_check_in' => (bool) ($data['flexible_check_in'] ?? false),
            'flexible_check_out' => (bool) ($data['flexible_check_out'] ?? false),
            'requires_host_time_approval' => (bool) ($data['requires_host_time_approval'] ?? false),
            'check_in_comment' => $data['check_in_comment'] ?? null,
            'check_out_comment' => $data['check_out_comment'] ?? null,
            'promo_code' => $data['promo_code'] ?? null,
            'currency' => strtoupper((string) ($place->currency ?: 'EUR')),
            'expires_at' => now()->addMinutes(20),
            'status' => BookingQuote::STATUS_DRAFT,
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function recalculationPayload(BookingQuote $quote, array $changes): array
    {
        $payload = [];

        foreach ([
            'check_in_date',
            'check_in_time',
            'check_out_date',
            'check_out_time',
            'check_in_window',
            'check_out_window',
            'guests_count',
            'early_check_in_requested',
            'late_check_out_requested',
            'flexible_check_in',
            'flexible_check_out',
            'requires_host_time_approval',
            'check_in_comment',
            'check_out_comment',
            'promo_code',
        ] as $field) {
            if (array_key_exists($field, $changes)) {
                $payload[$field] = $changes[$field];
            }
        }

        $checkIn = CarbonImmutable::parse($payload['check_in_date'] ?? $quote->check_in_date)->startOfDay();
        $checkOut = CarbonImmutable::parse($payload['check_out_date'] ?? $quote->check_out_date)->startOfDay();
        $nights = $this->stayLength->calculateNights($checkIn, $checkOut);

        return [
            ...$payload,
            'nights_count' => $nights,
            'chargeable_days_count' => $nights,
            'calendar_presence_days_count' => $this->stayLength->calculateCalendarPresenceDays($checkIn, $checkOut),
            'availability_status' => 'unchecked',
            'validation_status' => 'unchecked',
            'pricing_status' => 'unchecked',
            'status' => BookingQuote::STATUS_DRAFT,
            'expires_at' => now()->addMinutes(20),
        ];
    }

    private function loadPlace(SleepingPlace $place): SleepingPlace
    {
        return $place->loadMissing([
            'room',
            'property',
            'calendarSettings',
            'calendarDays',
            'pricingSettings',
            'datePrices',
            'pricingDiscountRules',
        ]);
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }
}
