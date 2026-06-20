<?php

namespace App\Services\BookingGuestIntake;

use App\Models\BookingGuestIntake;
use Illuminate\Support\Str;

class BookingGuestIntakeMessageService
{
    public function __construct(
        private readonly BookingGuestIntakePrivacyService $privacy,
    ) {}

    public function generateHostMessage(BookingGuestIntake $intake, string $locale): string
    {
        $parts = [
            __('guest_intake.generated.opening', [], $locale),
        ];

        if (filled($intake->trip_purpose)) {
            $parts[] = __('guest_intake.generated.purpose', [
                'purpose' => Str::lower($this->privacy->getSafeTripPurposeLabel($intake, $locale)),
            ], $locale);
        }

        if (! $intake->arrival_time_unknown && filled($intake->planned_arrival_time)) {
            $parts[] = __('guest_intake.generated.arrival', [
                'time' => $intake->planned_arrival_time,
            ], $locale);
        }

        if (filled($intake->baggage_level) || $intake->baggage_count) {
            $parts[] = __('guest_intake.generated.baggage', [
                'count' => (string) ($intake->baggage_count ?: 1),
            ], $locale);
        }

        if ($intake->needs_quiet || $intake->needs_workspace || $intake->needs_fast_wifi) {
            $parts[] = __('guest_intake.generated.work_quiet', [], $locale);
        }

        if ($intake->needs_registration || $intake->needs_work_documents || $intake->needs_invoice || $intake->needs_receipt || $intake->needs_contract) {
            $parts[] = __('guest_intake.generated.documents', [], $locale);
        }

        if (filled($intake->special_requests)) {
            $parts[] = $this->cleanHostMessage((string) $intake->special_requests);
        }

        return $this->cleanHostMessage(implode(' ', array_filter($parts)));
    }

    /**
     * @return list<string>
     */
    public function suggestMessageTemplates(BookingGuestIntake $intake, string $locale): array
    {
        $templates = [
            __('guest_intake.message_templates.basic', [], $locale),
            __('guest_intake.message_templates.arrival', [], $locale),
        ];

        if ($intake->needs_quiet || $intake->needs_workspace || $intake->needs_fast_wifi) {
            $templates[] = __('guest_intake.message_templates.work', [], $locale);
        }

        if ($intake->needs_registration || $intake->needs_work_documents) {
            $templates[] = __('guest_intake.message_templates.documents', [], $locale);
        }

        return array_values(array_unique($templates));
    }

    public function cleanHostMessage(string $message): string
    {
        return Str::of(strip_tags($message))
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->limit(1000, '')
            ->toString();
    }

    public function translatePurposeLabel(string $purpose, string $locale): string
    {
        return __("guest_intake.trip_purposes.{$purpose}", [], $locale);
    }
}
