<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAlert;
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
    public function reportProblem(User $guest, BookingCheckIn $checkIn, array $data): BookingCheckInProblemReport
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

        $checkIn->forceFill([
            'has_problem' => true,
            'problem_status' => 'open',
            'status' => 'check_in_problem',
        ])->save();

        $this->notifyHost($report);

        return $report->refresh();
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

    public function notifyHost(BookingCheckInProblemReport $report): void
    {
        app(BookingCheckInAlertService::class)->createAlert(
            $report->checkIn,
            'check_in_problem',
            $report->severity,
            ['problem_type' => $report->problem_type],
        );
    }

    public function markResolved(User $host, BookingCheckInProblemReport $report): BookingCheckInProblemReport
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
     * @return Collection<int, BookingCheckInProblemReport>
     */
    public function openSevereProblems(BookingCheckIn $checkIn): Collection
    {
        return $checkIn->problemReports()
            ->whereIn('severity', ['high', 'critical'])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->get();
    }
}
