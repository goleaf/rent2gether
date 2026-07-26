<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAlert;
use App\Models\BookingCheckInProblem;
use App\Models\BookingCheckInProblemReport;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingCheckInProblemService
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function reportProblem(User $guest, BookingCheckIn $checkIn, array $data): BookingCheckInProblem|BookingCheckInProblemReport
    {
        if ((int) $checkIn->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_your_booking'),
            ]);
        }

        $validated = Validator::make($data, [
            'problem_type' => ['required', 'string', 'max:80'],
            'severity' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo_paths' => ['nullable', 'array', 'max:8'],
            'photo_paths.*' => ['string', 'max:255'],
            'guest_wants_help' => ['nullable', 'boolean'],
            'guest_wants_relocation' => ['nullable', 'boolean'],
            'guest_wants_cancellation' => ['nullable', 'boolean'],
            'guest_wants_refund' => ['nullable', 'boolean'],
        ], [], __('check_in.validation.attributes'))->validate();

        $report = BookingCheckInProblemReport::query()->create([
            'booking_check_in_id' => $checkIn->id,
            'booking_id' => $checkIn->booking_id,
            'guest_user_id' => $checkIn->guest_user_id,
            'host_user_id' => $checkIn->host_user_id,
            'problem_type' => $validated['problem_type'],
            'severity' => $validated['severity'] ?? 'medium',
            'description' => $validated['description'] ?? null,
            'photo_paths_json' => $validated['photo_paths'] ?? [],
            'status' => 'open',
        ]);

        $this->notifyHost($report);

        if (! $this->shouldUsePointTenProblem($validated)) {
            $checkIn->forceFill([
                'has_problem' => true,
                'problem_status' => 'open',
                'status' => 'check_in_problem',
            ])->save();

            return $report->refresh();
        }

        $problem = BookingCheckInProblem::query()->create([
            'booking_check_in_id' => $checkIn->id,
            'booking_id' => $checkIn->booking_id,
            'guest_user_id' => $checkIn->guest_user_id,
            'host_user_id' => $checkIn->host_user_id,
            'property_id' => $checkIn->property_id,
            'room_id' => $checkIn->room_id,
            'sleeping_place_id' => $checkIn->sleeping_place_id,
            'problem_type' => $validated['problem_type'],
            'severity' => $validated['severity'] ?? 'medium',
            'status' => 'reported',
            'description' => $validated['description'] ?? null,
            'guest_wants_help' => (bool) ($validated['guest_wants_help'] ?? true),
            'guest_wants_relocation' => (bool) ($validated['guest_wants_relocation'] ?? false),
            'guest_wants_cancellation' => (bool) ($validated['guest_wants_cancellation'] ?? false),
            'guest_wants_refund' => (bool) ($validated['guest_wants_refund'] ?? false),
        ]);

        $checkIn->forceFill([
            'has_problem' => true,
            'problem_status' => 'reported',
            'problem_reported_at' => now(),
            'problem_summary' => $problem->description,
            'status' => 'problem_reported',
        ])->save();

        $this->notifySupportIfNeeded($problem->refresh(), $validated);

        if ($problem->problem_type === 'host_not_answering') {
            app(BookingCheckInHostUnresponsiveIntegrationService::class)->createCaseFromCheckInProblem($problem);
        }

        if ($problem->problem_type === 'listing_mismatch') {
            $problem->forceFill(['source_created_mismatch_report_id' => $problem->id])->save();
        }

        if ($problem->problem_type === 'unsafe_situation') {
            app(BookingCheckInComplaintIntegrationService::class)->createCaseFromCheckInProblem($problem);
        }

        $this->notifyHost($problem->refresh());

        return $problem->refresh();
    }

    /**
     * @param  array<int, string>  $photos
     */
    public function addProblemPhotos(User $guest, BookingCheckInProblemReport $report, array $photos): BookingCheckInProblemReport
    {
        if ((int) $report->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_your_booking'),
            ]);
        }

        $report->forceFill([
            'photo_paths_json' => collect($report->photo_paths_json ?? [])->merge($photos)->values()->all(),
        ])->save();

        return $report->refresh();
    }

    public function notifyHost(BookingCheckInProblem|BookingCheckInProblemReport $report): void
    {
        app(BookingCheckInAlertService::class)->createAlert(
            $report->checkIn,
            'check_in_problem',
            $report->severity,
            ['problem_type' => $report->problem_type],
        );

        if ($report instanceof BookingCheckInProblem) {
            app(BookingCheckInNotificationService::class)->notifyCheckInProblem($report);
        }
    }

    public function markResolved(User $host, BookingCheckInProblem|BookingCheckInProblemReport $report): BookingCheckInProblem|BookingCheckInProblemReport
    {
        if ((int) $report->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_in.validation.not_host_booking'));
        }

        $report->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
        ])->save();

        $checkIn = $report->checkIn;
        $hasOpen = $checkIn->problemReports()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->exists()
            || $checkIn->problems()
                ->whereNotIn('status', ['resolved', 'closed'])
                ->exists();

        if (! $hasOpen) {
            $checkIn->forceFill([
                'problem_status' => 'resolved',
                'status' => $checkIn->guest_confirmed_at || $checkIn->host_confirmed_at ? $checkIn->status : 'resolved',
            ])->save();
            $checkIn->alerts()
                ->whereNotIn('status', ['resolved', 'closed'])
                ->get()
                ->each(fn ($alert) => app(BookingCheckInAlertService::class)->resolveAlert($host, $alert));
        }

        return $report->refresh();
    }

    public function startRelocation(BookingCheckInProblem $problem): mixed
    {
        return app(BookingCheckInRelocationIntegrationService::class)->startRelocationFromCheckInProblem($problem);
    }

    public function startCancellation(BookingCheckInProblem $problem): mixed
    {
        $problem->forceFill(['status' => 'cancellation_started'])->save();

        return $problem->id;
    }

    public function createComplaintIfNeeded(BookingCheckInProblem $problem): mixed
    {
        if (! in_array($problem->problem_type, ['unsafe_situation', 'listing_mismatch'], true)) {
            return null;
        }

        return app(BookingCheckInComplaintIntegrationService::class)->createCaseFromCheckInProblem($problem);
    }

    public function escalateIfNeeded(BookingCheckInProblemReport $report): BookingCheckInAlert
    {
        return app(BookingCheckInAlertService::class)->createAlert(
            $report->checkIn,
            'check_in_escalation',
            $report->severity,
            ['problem_type' => $report->problem_type],
        );
    }

    /**
     * @return Collection<int, BookingCheckInProblem|BookingCheckInProblemReport>
     */
    public function openSevereProblems(BookingCheckIn $checkIn): Collection
    {
        $legacy = $checkIn->problemReports()
            ->whereIn('severity', ['high', 'critical'])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->get();

        return $legacy
            ->merge($checkIn->problems()
                ->whereIn('severity', ['high', 'urgent', 'emergency'])
                ->whereNotIn('status', ['resolved', 'closed'])
                ->get());
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function shouldUsePointTenProblem(array $validated): bool
    {
        if (array_key_exists('guest_wants_help', $validated)
            || array_key_exists('guest_wants_relocation', $validated)
            || array_key_exists('guest_wants_cancellation', $validated)
            || array_key_exists('guest_wants_refund', $validated)) {
            return true;
        }

        return in_array($validated['problem_type'], [
            'host_not_answering',
            'representative_not_answering',
            'listing_mismatch',
            'unsafe_situation',
            'wrong_sleeping_place',
            'sleeping_place_occupied',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function notifySupportIfNeeded(BookingCheckInProblem $problem, array $validated): void
    {
        $severity = (string) ($validated['severity'] ?? 'medium');
        $guestWantsHelp = (bool) ($validated['guest_wants_help'] ?? false);

        if (! $guestWantsHelp && ! in_array($severity, ['high', 'urgent', 'emergency', 'critical'], true)) {
            return;
        }

        app(BookingCheckInAlertService::class)->createAlert(
            $problem->checkIn,
            'support_attention_required',
            in_array($severity, ['low', 'medium', 'high', 'urgent', 'emergency', 'critical'], true) ? $severity : 'high',
            [
                'problem_type' => $problem->problem_type,
                'guest_wants_help' => $guestWantsHelp,
            ],
        );
    }
}
