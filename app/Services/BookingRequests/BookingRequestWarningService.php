<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\BookingRequestWarning;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingRequestWarningService
{
    /**
     * @return Collection<int, BookingRequestWarning>
     */
    public function generateWarnings(BookingRequest $request): Collection
    {
        $warnings = collect()
            ->when($this->detectLateNightArrival($request), fn (Collection $items, array $warning): Collection => $items->push($warning))
            ->when($this->detectEarlyCheckout($request), fn (Collection $items, array $warning): Collection => $items->push($warning))
            ->merge($this->detectVerificationWarnings($request))
            ->merge($this->detectGuestHistoryWarnings($request))
            ->when($this->detectLastMinuteWarning($request), fn (Collection $items, array $warning): Collection => $items->push($warning))
            ->merge($this->detectRulesCompatibilityWarnings($request))
            ->when($this->detectCleaningGapWarning($request), fn (Collection $items, array $warning): Collection => $items->push($warning))
            ->unique('warning_key')
            ->values();

        $request->warnings()->delete();

        return collect($request->warnings()->createMany($warnings->all()));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectLateNightArrival(BookingRequest $request): ?array
    {
        $time = $request->planned_arrival_time ?: $request->check_in_time;

        if (! $this->hourIsBetween($time, 22, 5)) {
            return null;
        }

        return $this->warning('late_night_arrival', BookingRequestWarning::SEVERITY_WARNING);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectEarlyCheckout(BookingRequest $request): ?array
    {
        $time = $request->planned_departure_time ?: $request->check_out_time;

        if (! $this->hourIsBetween($time, 0, 6)) {
            return null;
        }

        return $this->warning('very_early_checkout', BookingRequestWarning::SEVERITY_WARNING);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function detectVerificationWarnings(BookingRequest $request): Collection
    {
        $request->loadMissing('guest');
        $guest = $request->guest;
        $warnings = collect();

        if (! (bool) $guest->identity_verified && $guest->identity_verified_at === null) {
            $warnings->push($this->warning('identity_not_verified', BookingRequestWarning::SEVERITY_IMPORTANT));
        }

        if (! (bool) $guest->phone_verified && $guest->phone_verified_at === null) {
            $warnings->push($this->warning('phone_not_verified', BookingRequestWarning::SEVERITY_WARNING));
        }

        if ($guest->email_verified_at === null) {
            $warnings->push($this->warning('email_not_verified', BookingRequestWarning::SEVERITY_INFO));
        }

        return $warnings;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function detectGuestHistoryWarnings(BookingRequest $request): Collection
    {
        $request->loadMissing('guest.activitySummary');
        $guest = $request->guest;
        $summary = $guest->activitySummary;
        $warnings = collect();
        $completedStays = (int) ($summary?->completed_stays_as_guest ?? $guest->completed_stays_count ?? 0);
        $reviewsCount = (int) ($summary?->reviews_received_count ?? 0);
        $cancellations = (int) (($summary?->cancelled_by_guest_count ?? 0) + ($guest->cancellations_count ?? 0));

        if ($reviewsCount === 0) {
            $warnings->push($this->warning('no_reviews', BookingRequestWarning::SEVERITY_INFO));
        }

        if ($completedStays === 0) {
            $warnings->push($this->warning('new_guest', BookingRequestWarning::SEVERITY_INFO));
        }

        if ($cancellations > 0) {
            $warnings->push($this->warning('guest_had_cancellations', BookingRequestWarning::SEVERITY_WARNING, [
                'count' => $cancellations,
            ]));
        }

        if ((int) ($summary?->no_show_count ?? 0) > 0) {
            $warnings->push($this->warning('guest_had_no_show', BookingRequestWarning::SEVERITY_IMPORTANT));
        }

        if ((int) ($summary?->confirmed_complaints_count ?? 0) > 0) {
            $warnings->push($this->warning('guest_has_confirmed_complaints', BookingRequestWarning::SEVERITY_IMPORTANT));
        }

        if ((float) ($summary?->average_guest_rating ?? $guest->rating_as_guest ?? 5) < 3.5) {
            $warnings->push($this->warning('low_guest_rating', BookingRequestWarning::SEVERITY_WARNING));
        }

        return $warnings;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectLastMinuteWarning(BookingRequest $request): ?array
    {
        if (! $request->check_in_date || $request->check_in_date->greaterThan(CarbonImmutable::now()->addDay())) {
            return null;
        }

        $key = $request->request_type === BookingRequest::TYPE_SAME_DAY_URGENT
            ? 'same_day_urgent_request'
            : 'last_minute_request';

        return $this->warning($key, BookingRequestWarning::SEVERITY_URGENT);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function detectRulesCompatibilityWarnings(BookingRequest $request): Collection
    {
        $request->loadMissing(['guest', 'property', 'room', 'sleepingPlace']);
        $warnings = collect();
        $guest = $request->guest;
        $place = $request->sleepingPlace;

        if ((int) $request->guests_count > (int) ($place->max_guests_count ?: $place->max_guests ?: 1)) {
            $warnings->push($this->warning('too_many_guests', BookingRequestWarning::SEVERITY_BLOCKING, blocking: true));
        }

        if ((int) $place->max_nights > 0 && (int) $request->nights_count > (int) $place->max_nights) {
            $warnings->push($this->warning('above_max_stay', BookingRequestWarning::SEVERITY_IMPORTANT));
        }

        if ((bool) $guest->is_smoker && $this->ruleForbids($request->property?->rules, 'smoking')) {
            $warnings->push($this->warning('smoking_conflict', BookingRequestWarning::SEVERITY_IMPORTANT));
        }

        if ((bool) $guest->has_pets && $this->ruleForbids($request->property?->rules, 'pets')) {
            $warnings->push($this->warning('pet_conflict', BookingRequestWarning::SEVERITY_IMPORTANT));
        }

        if ($request->needs_early_check_in) {
            $warnings->push($this->warning('early_check_in_requested', BookingRequestWarning::SEVERITY_INFO));
        }

        if ($request->needs_late_checkout) {
            $warnings->push($this->warning('late_checkout_requested', BookingRequestWarning::SEVERITY_INFO));
        }

        if ($request->needs_residence_registration) {
            $warnings->push($this->warning('needs_registration_documents', BookingRequestWarning::SEVERITY_INFO));
        }

        if ($request->needs_reporting_documents) {
            $warnings->push($this->warning('needs_invoice_documents', BookingRequestWarning::SEVERITY_INFO));
        }

        return $warnings;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectCleaningGapWarning(BookingRequest $request): ?array
    {
        $request->loadMissing('bookingQuote.validationResults');

        $hasCleaningWarning = $request->bookingQuote?->validationResults
            ->contains(fn ($result): bool => in_array($result->validation_key, ['cleaning_gap_required', 'inspection_gap_required'], true));

        return $hasCleaningWarning
            ? $this->warning('cleaning_gap_conflict', BookingRequestWarning::SEVERITY_IMPORTANT)
            : null;
    }

    /**
     * @param  array<string, scalar|null>  $params
     * @return array<string, mixed>
     */
    private function warning(string $key, string $severity, array $params = [], bool $blocking = false): array
    {
        return [
            'warning_key' => $key,
            'severity' => $severity,
            'message_key' => "booking_requests.warnings.{$key}",
            'message_params_json' => $params,
            'blocking' => $blocking || $severity === BookingRequestWarning::SEVERITY_BLOCKING,
            'visible_to_host' => true,
            'visible_to_guest' => false,
        ];
    }

    private function hourIsBetween(?string $time, int $startHour, int $endHour): bool
    {
        if (blank($time)) {
            return false;
        }

        $hour = (int) substr($time, 0, 2);

        return $startHour > $endHour
            ? $hour >= $startHour || $hour <= $endHour
            : $hour >= $startHour && $hour <= $endHour;
    }

    private function ruleForbids(mixed $rules, string $key): bool
    {
        if (! is_array($rules)) {
            return false;
        }

        $value = $rules[$key] ?? $rules["{$key}_allowed"] ?? null;

        return $value === false || $value === 'forbidden' || $value === 'no';
    }
}
