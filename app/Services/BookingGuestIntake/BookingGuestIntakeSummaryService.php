<?php

namespace App\Services\BookingGuestIntake;

use App\Models\BookingGuestIntake;

class BookingGuestIntakeSummaryService
{
    public function __construct(
        private readonly BookingGuestIntakePrivacyService $privacy,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildHostSummary(BookingGuestIntake $intake): array
    {
        $safe = $this->privacy->filterForHost($intake);

        return [
            'trip_purpose' => $safe['trip_purpose'],
            'planned_arrival' => $this->plannedArrivalLabel($intake),
            'planned_departure' => $this->plannedDepartureLabel($intake),
            'early_check_in_requested' => $intake->early_check_in_requested,
            'late_check_out_requested' => $intake->late_check_out_requested,
            'baggage' => $this->baggageLabel($intake),
            'pet' => $intake->has_pet ? $this->optionLabel('pet_types', $intake->pet_type) : __('guest_intake.summary.no_pet'),
            'smoking' => is_null($intake->smokes) ? __('guest_intake.summary.not_specified') : ($intake->smokes ? __('guest_intake.summary.smokes') : __('guest_intake.summary.does_not_smoke')),
            'quiet_work_needs' => $this->quietWorkNeeds($intake),
            'documents_requested' => $safe['documents_requested'],
            'special_requests' => $safe['special_requests'],
            'message_to_host' => $safe['host_message'],
            'warnings' => $this->buildWarnings($intake),
            'required_confirmations' => $this->buildRequiredHostConfirmations($intake),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildGuestReviewSummary(BookingGuestIntake $intake): array
    {
        return [
            'host_will_see' => $this->buildHostSummary($intake),
            'private_details' => $this->privacy->filterForGuest($intake),
        ];
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function buildWarnings(BookingGuestIntake $intake): array
    {
        return $intake->warnings_json ?? [];
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function buildRequiredHostConfirmations(BookingGuestIntake $intake): array
    {
        return array_values(array_merge($intake->blocking_reasons_json ?? [], collect($intake->warnings_json ?? [])
            ->whereIn('key', [
                'early_check_in_unavailable',
                'late_check_out_unavailable',
                'documents_need_confirmation',
                'late_entry_unavailable',
                'self_check_in_unavailable',
            ])
            ->values()
            ->all()));
    }

    private function plannedArrivalLabel(BookingGuestIntake $intake): string
    {
        if ($intake->arrival_time_unknown) {
            return __('guest_intake.summary.time_unknown');
        }

        return $intake->planned_arrival_time ?: $intake->planned_arrival_window ?: __('guest_intake.summary.not_specified');
    }

    private function plannedDepartureLabel(BookingGuestIntake $intake): string
    {
        if ($intake->departure_time_unknown) {
            return __('guest_intake.summary.time_unknown');
        }

        return $intake->planned_departure_time ?: __('guest_intake.summary.not_specified');
    }

    private function baggageLabel(BookingGuestIntake $intake): string
    {
        if (blank($intake->baggage_level) && ! $intake->baggage_count) {
            return __('guest_intake.summary.not_specified');
        }

        $label = $this->optionLabel('baggage', $intake->baggage_level);

        if ($intake->baggage_count) {
            return __('guest_intake.summary.baggage_with_count', [
                'label' => $label,
                'count' => $intake->baggage_count,
            ]);
        }

        return $label;
    }

    /**
     * @return list<string>
     */
    private function quietWorkNeeds(BookingGuestIntake $intake): array
    {
        $needs = [];

        foreach (['needs_quiet', 'needs_workspace', 'needs_fast_wifi', 'needs_power_socket', 'needs_online_calls', 'needs_late_entry', 'needs_self_check_in'] as $field) {
            if ($intake->{$field}) {
                $needs[] = __("guest_intake.fields.{$field}");
            }
        }

        return $needs;
    }

    private function optionLabel(string $group, ?string $value): string
    {
        if (blank($value)) {
            return __('guest_intake.summary.not_specified');
        }

        return __("guest_intake.{$group}.{$value}");
    }
}
