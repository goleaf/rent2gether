<?php

namespace App\Services\BookingGuestIntake;

use App\Models\BookingGuestIntake;

class BookingGuestIntakePrivacyService
{
    /**
     * @return array<string, mixed>
     */
    public function filterForHost(BookingGuestIntake $intake): array
    {
        $locale = app()->getLocale();

        return [
            'trip_purpose' => $this->getSafeTripPurposeLabel($intake, $locale),
            'planned_arrival_date' => $intake->planned_arrival_date?->toDateString(),
            'planned_arrival_time' => $intake->arrival_time_unknown ? null : $intake->planned_arrival_time,
            'planned_arrival_window' => $intake->planned_arrival_window,
            'planned_departure_time' => $intake->departure_time_unknown ? null : $intake->planned_departure_time,
            'early_check_in_requested' => (bool) ($intake->needs_early_check_in || $intake->early_check_in_requested),
            'late_check_in_requested' => $intake->late_check_in_requested,
            'late_check_out_requested' => (bool) ($intake->needs_late_check_out || $intake->late_check_out_requested),
            'luggage_amount' => $intake->luggage_amount ?: $intake->baggage_level,
            'baggage_level' => $intake->luggage_amount ?: $intake->baggage_level,
            'baggage_count' => $intake->baggage_count,
            'has_large_suitcase' => $intake->has_large_suitcase,
            'has_pet' => $intake->has_pet,
            'pet_type' => $intake->pet_type,
            'pet_size' => $intake->pet_size,
            'smokes' => $intake->smokes,
            'needs_quiet' => $intake->needs_quiet,
            'needs_desk' => (bool) ($intake->needs_desk || $intake->needs_workspace),
            'needs_workspace' => (bool) ($intake->needs_desk || $intake->needs_workspace),
            'needs_fast_wifi' => $intake->needs_fast_wifi,
            'needs_power_socket' => $intake->needs_power_socket,
            'needs_online_calls' => $intake->needs_online_calls,
            'needs_late_entry' => $intake->needs_late_entry,
            'needs_self_check_in' => $intake->needs_self_check_in,
            'documents_requested' => $this->documentsRequested($intake),
            'special_requests' => $intake->special_requests,
            'message_to_host' => $intake->message_to_host ?: $intake->host_message ?: $intake->auto_generated_host_message,
            'host_message' => $intake->message_to_host ?: $intake->host_message ?: $intake->auto_generated_host_message,
            'warnings' => $intake->warnings_json ?? [],
            'blocking_reasons' => $intake->blocking_reasons_json ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(BookingGuestIntake $intake): array
    {
        return $intake->only([
            'trip_purpose',
            'trip_purpose_other',
            'trip_purpose_visibility',
            'planned_arrival_date',
            'planned_arrival_time',
            'planned_arrival_window',
            'planned_departure_time',
            'needs_early_check_in',
            'needs_late_check_out',
            'luggage_amount',
            'early_check_in_requested',
            'late_check_in_requested',
            'late_check_out_requested',
            'baggage_level',
            'baggage_count',
            'has_large_suitcase',
            'has_special_baggage',
            'has_pet',
            'pet_type',
            'pet_size',
            'smokes',
            'needs_quiet',
            'needs_desk',
            'needs_workspace',
            'needs_fast_wifi',
            'needs_power_socket',
            'needs_online_calls',
            'needs_registration',
            'needs_work_documents',
            'needs_invoice',
            'needs_receipt',
            'needs_contract',
            'special_requests',
            'message_to_host',
            'host_message',
            'auto_generated_host_message',
            'warnings_json',
            'blocking_reasons_json',
        ]);
    }

    public function shouldHideSensitiveTripPurpose(BookingGuestIntake $intake): bool
    {
        return $intake->trip_purpose === 'medical'
            && $intake->trip_purpose_visibility !== 'exact';
    }

    public function getSafeTripPurposeLabel(BookingGuestIntake $intake, string $locale): string
    {
        if (blank($intake->trip_purpose)) {
            return __('guest_intake.trip_purposes.not_selected', [], $locale);
        }

        if ($this->shouldHideSensitiveTripPurpose($intake)) {
            return __('guest_intake.trip_purposes.private_trip', [], $locale);
        }

        if ($intake->trip_purpose === 'other' && filled($intake->trip_purpose_other)) {
            return (string) $intake->trip_purpose_other;
        }

        return __("guest_intake.trip_purposes.{$intake->trip_purpose}", [], $locale);
    }

    private function documentsRequested(BookingGuestIntake $intake): bool
    {
        return $intake->needs_registration
            || $intake->needs_work_documents
            || $intake->needs_invoice
            || $intake->needs_receipt
            || $intake->needs_contract;
    }
}
