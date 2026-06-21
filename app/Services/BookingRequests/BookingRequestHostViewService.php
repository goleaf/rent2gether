<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\Users\UserProfileVisibilityService;

class BookingRequestHostViewService
{
    public function __construct(
        private readonly UserProfileVisibilityService $profiles,
        private readonly BookingRequestPrivacyService $privacy,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildHostView(User $host, BookingRequest $request): array
    {
        $safe = $this->privacy->filterForHost($host, $request);

        return [
            ...$safe,
            'compatibility' => $this->buildCompatibilitySummary($request),
            'warnings' => $this->buildWarningsSummary($request),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildGuestProfileSnapshot(User $guest): array
    {
        return $this->profiles->buildHostViewOfGuest($guest, $guest);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildGuestRatingSnapshot(User $guest): array
    {
        $guest->loadMissing('activitySummary');
        $summary = $guest->activitySummary;

        return [
            'rating' => $summary?->average_guest_rating ?: $guest->rating_as_guest,
            'completed_stays_count' => (int) ($summary?->completed_stays_as_guest ?? $guest->completed_stays_count ?? 0),
            'reviews_count' => (int) ($summary?->reviews_received_count ?? 0),
            'confirmed_complaints_count' => (int) ($summary?->confirmed_complaints_count ?? 0),
            'cancellations_count' => (int) (($summary?->cancelled_by_guest_count ?? 0) + ($guest->cancellations_count ?? 0)),
            'no_show_count' => (int) ($summary?->no_show_count ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildBookingContext(BookingRequest $request): array
    {
        return [
            'property_id' => $request->property_id,
            'room_id' => $request->room_id,
            'sleeping_place_id' => $request->sleeping_place_id,
            'check_in_date' => $request->check_in_date?->toDateString(),
            'check_in_time' => $request->check_in_time,
            'check_out_date' => $request->check_out_date?->toDateString(),
            'check_out_time' => $request->check_out_time,
            'nights_count' => (int) $request->nights_count,
            'chargeable_days_count' => (int) $request->chargeable_days_count,
            'calendar_presence_days_count' => (int) $request->calendar_presence_days_count,
            'guests_count' => (int) $request->guests_count,
            'trip_purpose' => $request->trip_purpose,
            'planned_arrival_time' => $request->planned_arrival_time,
            'planned_departure_time' => $request->planned_departure_time,
            'has_baggage' => (bool) $request->has_baggage,
            'needs_luggage_storage' => (bool) $request->needs_luggage_storage,
            'needs_early_check_in' => (bool) $request->needs_early_check_in,
            'needs_late_checkout' => (bool) $request->needs_late_checkout,
            'needs_residence_registration' => (bool) $request->needs_residence_registration,
            'needs_reporting_documents' => (bool) $request->needs_reporting_documents,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildCompatibilitySummary(BookingRequest $request): array
    {
        $request->loadMissing('compatibilityResults');

        return $request->compatibilityResults
            ->map(fn ($result): array => [
                'key' => $result->compatibility_key,
                'status' => $result->status,
                'severity' => $result->severity,
                'message_key' => $result->message_key,
                'message_params' => $result->message_params_json ?? [],
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildWarningsSummary(BookingRequest $request): array
    {
        $request->loadMissing('warnings');

        return $request->warnings
            ->where('visible_to_host', true)
            ->map(fn ($warning): array => [
                'key' => $warning->warning_key,
                'severity' => $warning->severity,
                'message_key' => $warning->message_key,
                'message_params' => $warning->message_params_json ?? [],
                'blocking' => (bool) $warning->blocking,
            ])
            ->values()
            ->all();
    }
}
